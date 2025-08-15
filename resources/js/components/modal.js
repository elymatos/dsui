/**
 * DSUI Modal Component
 * Alpine.js component with comprehensive focus management, accessibility, and HTMX integration
 */

DS.component.modal = (config = {}) => ({
    // Component state
    open: config.open || false,
    closable: config.closable !== false,
    closeOnOverlay: config.closeOnOverlay !== false,
    closeOnEscape: config.closeOnEscape !== false,
    scrollable: config.scrollable || false,
    centered: config.centered !== false,
    fullscreen: config.fullscreen || false,
    animation: config.animation || 'fade',
    persistent: config.persistent || false,
    loading: config.loading || false,
    
    // Focus management
    previousActiveElement: null,
    focusableElements: [],
    firstFocusableElement: null,
    lastFocusableElement: null,
    
    // Modal stack management
    modalLevel: 0,
    
    // Performance
    bodyScrollPosition: 0,
    
    /**
     * Initialize the component
     */
    init() {
        // Setup focus management
        this.setupFocusManagement();
        
        // Setup modal stack
        this.setupModalStack();
        
        // Setup HTMX integration
        this.setupHtmxIntegration();
        
        // Watch for open state changes
        this.$watch('open', (isOpen) => {
            if (isOpen) {
                this.onOpen();
            } else {
                this.onClose();
            }
        });
        
        // Watch for loading state changes
        this.$watch('loading', (isLoading) => {
            this.onLoadingChange(isLoading);
        });
        
        // Auto-open if configured
        if (config.autoOpen) {
            this.$nextTick(() => {
                this.open();
            });
        }
    },
    
    /**
     * Open the modal
     */
    open() {
        if (this.open) return;
        
        // Store current active element for focus restoration
        this.previousActiveElement = document.activeElement;
        
        // Prevent body scroll
        this.preventBodyScroll();
        
        // Add to modal stack
        this.addToModalStack();
        
        // Set open state
        this.open = true;
        
        // Emit open event
        this.$dispatch('modal-open', {
            modalId: this.$el.id,
            level: this.modalLevel
        });
        
        // Focus management after transition
        this.$nextTick(() => {
            this.setupInitialFocus();
        });
    },
    
    /**
     * Close the modal
     */
    close() {
        if (!this.open || this.persistent) return;
        
        // Emit close event (can be prevented)
        const closeEvent = new CustomEvent('modal-before-close', {
            detail: { modalId: this.$el.id },
            cancelable: true
        });
        
        this.$el.dispatchEvent(closeEvent);
        
        if (closeEvent.defaultPrevented) {
            return;
        }
        
        // Set closed state
        this.open = false;
        
        // Restore body scroll
        this.restoreBodyScroll();
        
        // Remove from modal stack
        this.removeFromModalStack();
        
        // Restore focus
        this.restoreFocus();
        
        // Emit closed event
        this.$dispatch('modal-close', {
            modalId: this.$el.id,
            level: this.modalLevel
        });
    },
    
    /**
     * Toggle modal open/closed
     */
    toggle() {
        this.open ? this.close() : this.open();
    },
    
    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Define focusable element selectors
        this.focusableSelectors = [
            'button:not([disabled])',
            'input:not([disabled])',
            'textarea:not([disabled])',
            'select:not([disabled])',
            'a[href]',
            '[tabindex]:not([tabindex="-1"])',
            '[contenteditable="true"]'
        ].join(', ');
    },
    
    /**
     * Setup initial focus when modal opens
     */
    setupInitialFocus() {
        this.updateFocusableElements();
        
        // Focus first focusable element or modal itself
        if (this.firstFocusableElement) {
            this.firstFocusableElement.focus();
        } else {
            this.$el.focus();
        }
    },
    
    /**
     * Update list of focusable elements
     */
    updateFocusableElements() {
        this.focusableElements = Array.from(
            this.$el.querySelectorAll(this.focusableSelectors)
        ).filter(el => this.isVisible(el));
        
        this.firstFocusableElement = this.focusableElements[0] || null;
        this.lastFocusableElement = this.focusableElements[this.focusableElements.length - 1] || null;
    },
    
    /**
     * Focus first element (used by focus trap)
     */
    focusFirstElement() {
        if (this.firstFocusableElement) {
            this.firstFocusableElement.focus();
        }
    },
    
    /**
     * Focus last element (used by focus trap)
     */
    focusLastElement() {
        if (this.lastFocusableElement) {
            this.lastFocusableElement.focus();
        }
    },
    
    /**
     * Handle tab key for focus trapping
     */
    handleTabKey(event) {
        if (!this.open) return;
        
        this.updateFocusableElements();
        
        if (this.focusableElements.length === 0) {
            event.preventDefault();
            return;
        }
        
        if (event.shiftKey) {
            // Shift + Tab (backward)
            if (document.activeElement === this.firstFocusableElement) {
                event.preventDefault();
                this.lastFocusableElement?.focus();
            }
        } else {
            // Tab (forward)
            if (document.activeElement === this.lastFocusableElement) {
                event.preventDefault();
                this.firstFocusableElement?.focus();
            }
        }
    },
    
    /**
     * Restore focus to previous element
     */
    restoreFocus() {
        if (this.previousActiveElement && this.isVisible(this.previousActiveElement)) {
            this.previousActiveElement.focus();
        }
        this.previousActiveElement = null;
    },
    
    /**
     * Check if element is visible
     */
    isVisible(element) {
        if (!element) return false;
        
        const style = window.getComputedStyle(element);
        return style.display !== 'none' && 
               style.visibility !== 'hidden' && 
               element.offsetParent !== null;
    },
    
    /**
     * Prevent body scroll
     */
    preventBodyScroll() {
        this.bodyScrollPosition = window.pageYOffset;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${this.bodyScrollPosition}px`;
        document.body.style.width = '100%';
        document.body.classList.add('ds-modal-open');
    },
    
    /**
     * Restore body scroll
     */
    restoreBodyScroll() {
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        document.body.classList.remove('ds-modal-open');
        window.scrollTo(0, this.bodyScrollPosition);
    },
    
    /**
     * Setup modal stack management
     */
    setupModalStack() {
        if (!window.dsModalStack) {
            window.dsModalStack = [];
        }
    },
    
    /**
     * Add modal to stack
     */
    addToModalStack() {
        this.modalLevel = window.dsModalStack.length;
        window.dsModalStack.push(this);
        this.updateModalZIndex();
    },
    
    /**
     * Remove modal from stack
     */
    removeFromModalStack() {
        const index = window.dsModalStack.indexOf(this);
        if (index > -1) {
            window.dsModalStack.splice(index, 1);
        }
        
        // Update z-indexes of remaining modals
        window.dsModalStack.forEach((modal, index) => {
            modal.modalLevel = index;
            modal.updateModalZIndex();
        });
    },
    
    /**
     * Update modal z-index based on stack level
     */
    updateModalZIndex() {
        const baseZIndex = 1050; // Base z-index for modals
        this.$el.style.zIndex = baseZIndex + (this.modalLevel * 10);
    },
    
    /**
     * Setup HTMX integration
     */
    setupHtmxIntegration() {
        if (config.htmxAction) {
            // Load content via HTMX when modal opens
            this.$el.addEventListener('modal-open', () => {
                this.loadContent();
            });
        }
    },
    
    /**
     * Load content via HTMX
     */
    async loadContent() {
        if (!config.htmxAction) return;
        
        this.loading = true;
        
        try {
            this.$dispatch('htmx-trigger', {
                action: config.htmxAction,
                target: this.$el.querySelector('.ds-modal-content-wrapper'),
                data: config.htmxData || {}
            });
        } catch (error) {
            console.error('Modal content loading failed:', error);
            this.$dispatch('modal-load-error', { error });
        } finally {
            // Loading will be set to false by HTMX completion
        }
    },
    
    /**
     * Handle modal open event
     */
    onOpen() {
        // Add escape key listener
        document.addEventListener('keydown', this.handleEscapeKey.bind(this));
        document.addEventListener('keydown', this.handleTabKey.bind(this));
        
        // Announce to screen readers
        this.announceToScreenReaders('Modal opened');
        
        // Update ARIA attributes
        this.$el.setAttribute('aria-hidden', 'false');
    },
    
    /**
     * Handle modal close event
     */
    onClose() {
        // Remove event listeners
        document.removeEventListener('keydown', this.handleEscapeKey.bind(this));
        document.removeEventListener('keydown', this.handleTabKey.bind(this));
        
        // Announce to screen readers
        this.announceToScreenReaders('Modal closed');
        
        // Update ARIA attributes
        this.$el.setAttribute('aria-hidden', 'true');
    },
    
    /**
     * Handle escape key
     */
    handleEscapeKey(event) {
        if (event.key === 'Escape' && this.open && this.closeOnEscape && !this.persistent) {
            // Only close the topmost modal
            if (window.dsModalStack[window.dsModalStack.length - 1] === this) {
                this.close();
            }
        }
    },
    
    /**
     * Handle loading state changes
     */
    onLoadingChange(isLoading) {
        if (isLoading) {
            this.announceToScreenReaders('Loading content');
        }
    },
    
    /**
     * Announce to screen readers
     */
    announceToScreenReaders(message) {
        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.className = 'sr-only';
        announcer.textContent = message;
        
        document.body.appendChild(announcer);
        
        setTimeout(() => {
            document.body.removeChild(announcer);
        }, 1000);
    },
    
    /**
     * Get modal data for external access
     */
    getModalData() {
        return {
            open: this.open,
            modalLevel: this.modalLevel,
            loading: this.loading,
            persistent: this.persistent,
            focusableElements: this.focusableElements.length
        };
    }
});