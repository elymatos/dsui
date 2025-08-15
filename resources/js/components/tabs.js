// DSUI Tabs Component
DS.component.tabs = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core state
    tabs: config.tabs || [],
    activeTab: config.activeTab || '',
    orientation: config.orientation || 'horizontal',
    lazy: config.lazy !== undefined ? config.lazy : true,
    closable: config.closable || false,
    addable: config.addable || false,
    animated: config.animated !== undefined ? config.animated : true,
    scrollable: config.scrollable || false,
    
    // Scroll state
    hasOverflow: false,
    canScrollLeft: false,
    canScrollRight: false,
    
    // Loading state
    loadingTabs: new Set(),
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Set initial active tab if none specified
        if (!this.activeTab && this.tabs.length > 0) {
            this.activeTab = this.tabs[0].id;
        }
        
        // Initialize scroll state for scrollable tabs
        if (this.scrollable) {
            this.$nextTick(() => {
                this.updateScrollState();
                this.setupScrollObserver();
            });
        }
        
        // Auto-load first tab if lazy loading is disabled
        if (!this.lazy && this.tabs.length > 0) {
            this.tabs.forEach(tab => {
                if (!tab.loaded && !tab.loading) {
                    this.loadTabContent(tab.id);
                }
            });
        }
        
        // Load active tab content immediately
        this.$nextTick(() => {
            if (this.activeTab) {
                const activeTabData = this.tabs.find(tab => tab.id === this.activeTab);
                if (activeTabData && activeTabData.lazy && !activeTabData.loaded) {
                    this.loadTabContent(this.activeTab);
                }
            }
        });
        
        // Setup keyboard navigation
        this.setupKeyboardNavigation();
        
        console.log('DSUI: Tabs component initialized', {
            tabs: this.tabs.length,
            activeTab: this.activeTab,
            orientation: this.orientation
        });
    },
    
    // Tab Selection
    selectTab(tabId) {
        const tab = this.tabs.find(t => t.id === tabId);
        if (!tab || tab.disabled) return;
        
        const previousTab = this.activeTab;
        this.activeTab = tabId;
        
        // Load content if lazy and not loaded
        if (tab.lazy && !tab.loaded && !tab.loading) {
            this.loadTabContent(tabId);
        }
        
        // Update URL hash if configured
        if (config.updateUrl) {
            window.history.replaceState(null, null, `#${tabId}`);
        }
        
        // Emit events
        this.$dispatch('ds-tab-change', {
            activeTab: tabId,
            previousTab: previousTab,
            tab: tab
        });
        
        // Scroll active tab into view if scrollable
        if (this.scrollable) {
            this.$nextTick(() => {
                this.scrollActiveTabIntoView();
            });
        }
        
        console.log('DSUI: Tab selected', { tabId, tab });
    },
    
    // Content Loading
    async loadTabContent(tabId) {
        const tab = this.tabs.find(t => t.id === tabId);
        if (!tab || tab.loaded || tab.loading) return;
        
        // Set loading state
        tab.loading = true;
        tab.error = false;
        tab.errorMessage = '';
        this.loadingTabs.add(tabId);
        
        try {
            let content = '';
            
            // Load content from URL if provided
            if (tab.url) {
                const response = await fetch(tab.url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                content = await response.text();
            } else if (config.onLoadContent) {
                // Custom content loader
                content = await config.onLoadContent(tab);
            } else if (tab.content) {
                // Static content
                content = tab.content;
            }
            
            // Update tab state
            tab.content = content;
            tab.loaded = true;
            tab.loading = false;
            
            // Emit success event
            this.$dispatch('ds-tab-loaded', {
                tabId: tabId,
                tab: tab,
                content: content
            });
            
            // Re-initialize Alpine components in new content
            this.$nextTick(() => {
                const panel = document.getElementById(`panel-${tabId}`);
                if (panel && window.Alpine) {
                    Alpine.initTree(panel);
                }
            });
            
        } catch (error) {
            // Set error state
            tab.loading = false;
            tab.error = true;
            tab.errorMessage = error.message || 'Failed to load content';
            
            // Emit error event
            this.$dispatch('ds-tab-error', {
                tabId: tabId,
                tab: tab,
                error: error
            });
            
            console.error('DSUI: Tab content loading failed', { tabId, error });
        } finally {
            this.loadingTabs.delete(tabId);
        }
    },
    
    // Retry loading tab content
    retryTabContent(tabId) {
        const tab = this.tabs.find(t => t.id === tabId);
        if (!tab) return;
        
        // Reset error state
        tab.error = false;
        tab.errorMessage = '';
        tab.loaded = false;
        
        // Retry loading
        this.loadTabContent(tabId);
    },
    
    // Tab Management
    closeTab(tabId) {
        if (!this.closable || this.tabs.length <= 1) return;
        
        const tabIndex = this.tabs.findIndex(t => t.id === tabId);
        if (tabIndex === -1) return;
        
        const tab = this.tabs[tabIndex];
        
        // Emit close event (can be cancelled)
        const event = new CustomEvent('ds-tab-close', {
            detail: { tabId, tab, canCancel: true },
            cancelable: true
        });
        this.$el.dispatchEvent(event);
        
        if (event.defaultPrevented) return;
        
        // Remove tab
        this.tabs.splice(tabIndex, 1);
        
        // Update active tab if needed
        if (this.activeTab === tabId) {
            if (this.tabs.length > 0) {
                // Select next tab, or previous if closing last tab
                const newIndex = tabIndex >= this.tabs.length ? this.tabs.length - 1 : tabIndex;
                this.selectTab(this.tabs[newIndex].id);
            } else {
                this.activeTab = '';
            }
        }
        
        // Update scroll state
        if (this.scrollable) {
            this.$nextTick(() => {
                this.updateScrollState();
            });
        }
        
        this.$dispatch('ds-tab-closed', { tabId, tab });
    },
    
    addTab() {
        if (!this.addable) return;
        
        const newTabId = `tab-${Date.now()}`;
        const newTab = {
            id: newTabId,
            label: `New Tab ${this.tabs.length + 1}`,
            content: '',
            icon: null,
            disabled: false,
            closable: true,
            badge: null,
            url: null,
            lazy: this.lazy,
            loaded: !this.lazy
        };
        
        // Emit add event (can modify tab data)
        const event = new CustomEvent('ds-tab-add', {
            detail: { tab: newTab },
            cancelable: true
        });
        this.$el.dispatchEvent(event);
        
        if (event.defaultPrevented) return;
        
        // Add tab
        this.tabs.push(newTab);
        
        // Activate new tab
        this.selectTab(newTabId);
        
        // Update scroll state
        if (this.scrollable) {
            this.$nextTick(() => {
                this.updateScrollState();
                this.scrollActiveTabIntoView();
            });
        }
        
        this.$dispatch('ds-tab-added', { tab: newTab });
    },
    
    // Keyboard Navigation
    setupKeyboardNavigation() {
        // Handle keyboard navigation on tab buttons
        this.$el.addEventListener('keydown', (e) => {
            this.handleTabKeydown(e, this.activeTab);
        });
    },
    
    handleTabKeydown(event, tabId) {
        const { key, target } = event;
        const tabButtons = Array.from(this.$el.querySelectorAll('[role="tab"]:not([disabled])'));
        const currentIndex = tabButtons.findIndex(button => button.id === `tab-${tabId}`);
        
        let newIndex = currentIndex;
        
        switch (key) {
            case 'ArrowRight':
            case 'ArrowDown':
                event.preventDefault();
                newIndex = (currentIndex + 1) % tabButtons.length;
                break;
                
            case 'ArrowLeft':
            case 'ArrowUp':
                event.preventDefault();
                newIndex = currentIndex === 0 ? tabButtons.length - 1 : currentIndex - 1;
                break;
                
            case 'Home':
                event.preventDefault();
                newIndex = 0;
                break;
                
            case 'End':
                event.preventDefault();
                newIndex = tabButtons.length - 1;
                break;
                
            case 'Enter':
            case ' ':
                event.preventDefault();
                const targetTabId = target.id.replace('tab-', '');
                this.selectTab(targetTabId);
                return;
                
            default:
                return;
        }
        
        // Focus and select new tab
        const newButton = tabButtons[newIndex];
        if (newButton) {
            newButton.focus();
            const newTabId = newButton.id.replace('tab-', '');
            this.selectTab(newTabId);
        }
    },
    
    // Scrolling (for scrollable tabs)
    updateScrollState() {
        if (!this.scrollable) return;
        
        const navWrapper = this.$el.querySelector('.ds-tabs-nav-wrapper');
        if (!navWrapper) return;
        
        this.hasOverflow = navWrapper.scrollWidth > navWrapper.clientWidth;
        this.canScrollLeft = navWrapper.scrollLeft > 0;
        this.canScrollRight = navWrapper.scrollLeft < (navWrapper.scrollWidth - navWrapper.clientWidth);
    },
    
    setupScrollObserver() {
        const navWrapper = this.$el.querySelector('.ds-tabs-nav-wrapper');
        if (!navWrapper) return;
        
        // Update scroll state on scroll
        navWrapper.addEventListener('scroll', () => {
            this.updateScrollState();
        });
        
        // Update on resize
        const resizeObserver = new ResizeObserver(() => {
            this.updateScrollState();
        });
        resizeObserver.observe(navWrapper);
    },
    
    scrollTabs(direction) {
        const navWrapper = this.$el.querySelector('.ds-tabs-nav-wrapper');
        if (!navWrapper) return;
        
        const scrollAmount = 120; // pixels
        const currentScroll = navWrapper.scrollLeft;
        const newScroll = direction === 'left' 
            ? currentScroll - scrollAmount 
            : currentScroll + scrollAmount;
        
        navWrapper.scrollTo({
            left: newScroll,
            behavior: 'smooth'
        });
    },
    
    scrollActiveTabIntoView() {
        const activeButton = this.$el.querySelector(`#tab-${this.activeTab}`);
        const navWrapper = this.$el.querySelector('.ds-tabs-nav-wrapper');
        
        if (!activeButton || !navWrapper) return;
        
        const buttonRect = activeButton.getBoundingClientRect();
        const wrapperRect = navWrapper.getBoundingClientRect();
        
        // Check if button is outside visible area
        if (buttonRect.left < wrapperRect.left || buttonRect.right > wrapperRect.right) {
            activeButton.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    },
    
    // Utility Methods
    getTab(tabId) {
        return this.tabs.find(t => t.id === tabId);
    },
    
    isTabActive(tabId) {
        return this.activeTab === tabId;
    },
    
    isTabLoading(tabId) {
        return this.loadingTabs.has(tabId);
    },
    
    getActiveTab() {
        return this.getTab(this.activeTab);
    },
    
    // Public API
    activateTab(tabId) {
        this.selectTab(tabId);
    },
    
    reloadTab(tabId) {
        const tab = this.getTab(tabId);
        if (tab) {
            tab.loaded = false;
            tab.error = false;
            this.loadTabContent(tabId);
        }
    },
    
    updateTab(tabId, updates) {
        const tab = this.getTab(tabId);
        if (tab) {
            Object.assign(tab, updates);
            this.$dispatch('ds-tab-updated', { tabId, tab, updates });
        }
    }
});