/**
 * DSUI Link Component
 * Alpine.js component for enhanced link functionality with tracking and accessibility
 */

DS.component.link = (config = {}) => ({
    // Component state
    href: config.href || '#',
    target: config.target || null,
    external: config.external || false,
    download: config.download || false,
    visited: config.visited || false,
    
    // Optional features
    trackClicks: config.trackClicks || false,
    confirmExternal: config.confirmExternal || false,
    preloadOnHover: config.preloadOnHover || false,
    
    // Internal state
    hovered: false,
    clicked: false,
    preloaded: false,
    
    /**
     * Initialize the component
     */
    init() {
        // Track visited state
        this.checkVisitedState();
        
        // Setup click tracking
        if (this.trackClicks) {
            this.setupClickTracking();
        }
        
        // Setup external link confirmation
        if (this.confirmExternal && this.external) {
            this.setupExternalConfirmation();
        }
        
        // Setup hover preloading
        if (this.preloadOnHover && !this.external) {
            this.setupHoverPreloading();
        }
        
        // Setup accessibility enhancements
        this.setupAccessibilityFeatures();
    },
    
    /**
     * Check if link has been visited
     */
    checkVisitedState() {
        // Use browser's visited pseudo-class detection (limited for security)
        // This is mainly for internal state tracking
        const visitedLinks = JSON.parse(localStorage.getItem('ds-visited-links') || '[]');
        this.visited = visitedLinks.includes(this.href);
    },
    
    /**
     * Mark link as visited
     */
    markAsVisited() {
        const visitedLinks = JSON.parse(localStorage.getItem('ds-visited-links') || '[]');
        if (!visitedLinks.includes(this.href)) {
            visitedLinks.push(this.href);
            localStorage.setItem('ds-visited-links', JSON.stringify(visitedLinks));
            this.visited = true;
        }
    },
    
    /**
     * Setup click tracking
     */
    setupClickTracking() {
        this.$el.addEventListener('click', (event) => {
            this.handleClick(event);
        });
    },
    
    /**
     * Handle link click
     */
    handleClick(event) {
        this.clicked = true;
        this.markAsVisited();
        
        // Emit tracking event
        this.$dispatch('link-click', {
            href: this.href,
            external: this.external,
            download: this.download,
            target: this.target,
            timestamp: new Date().toISOString()
        });
        
        // HTMX integration for internal links
        if (!this.external && !this.download && config.htmxAction) {
            event.preventDefault();
            this.$dispatch('htmx-trigger', {
                action: config.htmxAction,
                data: { href: this.href }
            });
        }
    },
    
    /**
     * Setup external link confirmation
     */
    setupExternalConfirmation() {
        this.$el.addEventListener('click', (event) => {
            if (this.external) {
                const confirmed = confirm(
                    `You are about to leave this site and go to:\n${this.href}\n\nDo you want to continue?`
                );
                
                if (!confirmed) {
                    event.preventDefault();
                    return false;
                }
            }
        });
    },
    
    /**
     * Setup hover preloading for internal links
     */
    setupHoverPreloading() {
        this.$el.addEventListener('mouseenter', () => {
            this.hovered = true;
            if (!this.preloaded && !this.external) {
                this.preloadLink();
            }
        });
        
        this.$el.addEventListener('mouseleave', () => {
            this.hovered = false;
        });
    },
    
    /**
     * Preload link destination
     */
    preloadLink() {
        if (this.preloaded || this.external) return;
        
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = this.href;
        document.head.appendChild(link);
        
        this.preloaded = true;
        
        this.$dispatch('link-preloaded', {
            href: this.href
        });
    },
    
    /**
     * Setup accessibility features
     */
    setupAccessibilityFeatures() {
        // Add keyboard navigation enhancements
        this.$el.addEventListener('keydown', (event) => {
            // Open external links with Ctrl+Enter or Cmd+Enter
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                if (this.external) {
                    event.preventDefault();
                    window.open(this.href, this.target || '_blank');
                }
            }
        });
        
        // Announce link type to screen readers on focus
        this.$el.addEventListener('focus', () => {
            this.announceLink();
        });
    },
    
    /**
     * Announce link information to screen readers
     */
    announceLink() {
        let announcement = '';
        
        if (this.external) {
            announcement += 'External link. ';
        }
        
        if (this.download) {
            announcement += 'Download link. ';
        }
        
        if (announcement) {
            // Create temporary announcement element
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.className = 'sr-only';
            announcer.textContent = announcement;
            
            document.body.appendChild(announcer);
            
            // Remove after announcement
            setTimeout(() => {
                document.body.removeChild(announcer);
            }, 1000);
        }
    },
    
    /**
     * Get link analytics data
     */
    getAnalyticsData() {
        return {
            href: this.href,
            external: this.external,
            download: this.download,
            visited: this.visited,
            clicked: this.clicked,
            hovered: this.hovered,
            preloaded: this.preloaded
        };
    },
    
    /**
     * Update link href
     */
    updateHref(newHref) {
        this.href = newHref;
        this.$el.href = newHref;
        
        // Re-check if external
        this.external = this.isExternalUrl(newHref);
        
        // Reset states
        this.visited = false;
        this.clicked = false;
        this.preloaded = false;
        
        this.checkVisitedState();
    },
    
    /**
     * Check if URL is external (client-side version)
     */
    isExternalUrl(url) {
        try {
            const linkUrl = new URL(url, window.location.origin);
            return linkUrl.hostname !== window.location.hostname;
        } catch {
            return false;
        }
    },
    
    /**
     * Open link programmatically
     */
    openLink(newTab = false) {
        if (newTab || this.external) {
            window.open(this.href, this.target || '_blank');
        } else {
            window.location.href = this.href;
        }
        
        this.handleClick(new Event('click'));
    },
    
    /**
     * Copy link to clipboard
     */
    async copyLink() {
        try {
            await navigator.clipboard.writeText(this.href);
            
            this.$dispatch('link-copied', {
                href: this.href,
                success: true
            });
            
            return true;
        } catch (error) {
            this.$dispatch('link-copy-error', {
                href: this.href,
                error: error.message
            });
            
            return false;
        }
    }
});