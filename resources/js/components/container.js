/**
 * DSUI Container Component
 * Alpine.js component for responsive container with breakpoint awareness
 */

DS.component.container = (config = {}) => ({
    // Component state
    maxWidth: config.maxWidth || 'desktop',
    fluid: config.fluid || false,
    centered: config.centered !== false,
    padding: config.padding || 'normal',
    margin: config.margin || 'none',
    breakpoint: config.breakpoint || null,
    
    // Responsive state
    currentBreakpoint: '',
    containerWidth: 0,
    viewportWidth: 0,
    
    // Optional features
    responsive: config.responsive !== false,
    trackResize: config.trackResize || false,
    
    /**
     * Initialize the component
     */
    init() {
        // Setup responsive behavior
        if (this.responsive) {
            this.setupResponsiveBehavior();
        }
        
        // Setup resize tracking
        if (this.trackResize) {
            this.setupResizeTracking();
        }
        
        // Initial breakpoint detection
        this.detectBreakpoint();
        this.updateContainerWidth();
        
        // Setup intersection observer for visibility tracking
        this.setupVisibilityTracking();
    },
    
    /**
     * Setup responsive behavior
     */
    setupResponsiveBehavior() {
        // Watch for viewport changes
        window.addEventListener('resize', this.debounce(() => {
            this.detectBreakpoint();
            this.updateContainerWidth();
            this.onBreakpointChange();
        }, 250));
        
        // Initial setup
        this.detectBreakpoint();
    },
    
    /**
     * Detect current breakpoint
     */
    detectBreakpoint() {
        const width = window.innerWidth;
        let breakpoint = 'mobile';
        
        if (width >= 1408) {
            breakpoint = 'fullhd';
        } else if (width >= 1216) {
            breakpoint = 'widescreen';
        } else if (width >= 1024) {
            breakpoint = 'desktop';
        } else if (width >= 769) {
            breakpoint = 'tablet';
        }
        
        const previousBreakpoint = this.currentBreakpoint;
        this.currentBreakpoint = breakpoint;
        this.viewportWidth = width;
        
        // Emit breakpoint change event
        if (previousBreakpoint && previousBreakpoint !== breakpoint) {
            this.$dispatch('container-breakpoint-change', {
                from: previousBreakpoint,
                to: breakpoint,
                width: width
            });
        }
    },
    
    /**
     * Update container width measurement
     */
    updateContainerWidth() {
        this.containerWidth = this.$el.offsetWidth;
        
        this.$dispatch('container-resize', {
            containerWidth: this.containerWidth,
            viewportWidth: this.viewportWidth,
            breakpoint: this.currentBreakpoint
        });
    },
    
    /**
     * Setup resize tracking
     */
    setupResizeTracking() {
        const resizeObserver = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const { width, height } = entry.contentRect;
                this.containerWidth = width;
                
                this.$dispatch('container-content-resize', {
                    width: width,
                    height: height,
                    breakpoint: this.currentBreakpoint
                });
            }
        });
        
        resizeObserver.observe(this.$el);
    },
    
    /**
     * Setup visibility tracking
     */
    setupVisibilityTracking() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                this.$dispatch('container-visibility-change', {
                    visible: entry.isIntersecting,
                    ratio: entry.intersectionRatio,
                    breakpoint: this.currentBreakpoint
                });
            });
        }, {
            threshold: [0, 0.25, 0.5, 0.75, 1]
        });
        
        observer.observe(this.$el);
    },
    
    /**
     * Handle breakpoint changes
     */
    onBreakpointChange() {
        // Update container classes based on breakpoint
        this.updateBreakpointClasses();
        
        // Emit custom event for other components
        this.$dispatch('breakpoint-change', {
            breakpoint: this.currentBreakpoint,
            viewportWidth: this.viewportWidth,
            containerWidth: this.containerWidth
        });
    },
    
    /**
     * Update CSS classes based on current breakpoint
     */
    updateBreakpointClasses() {
        // Remove existing breakpoint classes
        const breakpointClasses = ['ds-container--mobile', 'ds-container--tablet', 'ds-container--desktop', 'ds-container--widescreen', 'ds-container--fullhd'];
        breakpointClasses.forEach(className => {
            this.$el.classList.remove(className);
        });
        
        // Add current breakpoint class
        this.$el.classList.add(`ds-container--${this.currentBreakpoint}`);
    },
    
    /**
     * Toggle fluid mode
     */
    toggleFluid() {
        this.fluid = !this.fluid;
        
        if (this.fluid) {
            this.$el.classList.remove('container');
            this.$el.classList.add('container-fluid');
        } else {
            this.$el.classList.remove('container-fluid');
            this.$el.classList.add('container');
        }
        
        this.updateContainerWidth();
    },
    
    /**
     * Set max width
     */
    setMaxWidth(maxWidth) {
        // Remove existing max-width classes
        const maxWidthClasses = ['is-widescreen', 'is-fullhd', 'is-max-desktop', 'is-max-widescreen'];
        maxWidthClasses.forEach(className => {
            this.$el.classList.remove(className);
        });
        
        // Add new max-width class
        if (maxWidth !== 'desktop') {
            this.$el.classList.add(`is-${maxWidth}`);
        }
        
        this.maxWidth = maxWidth;
        this.updateContainerWidth();
    },
    
    /**
     * Set padding
     */
    setPadding(padding) {
        // Remove existing padding classes
        const paddingClasses = ['ds-container--padding-small', 'ds-container--padding-normal', 'ds-container--padding-medium', 'ds-container--padding-large'];
        paddingClasses.forEach(className => {
            this.$el.classList.remove(className);
        });
        
        // Add new padding class
        if (padding !== 'none') {
            this.$el.classList.add(`ds-container--padding-${padding}`);
        }
        
        this.padding = padding;
    },
    
    /**
     * Get container metrics
     */
    getMetrics() {
        return {
            containerWidth: this.containerWidth,
            viewportWidth: this.viewportWidth,
            currentBreakpoint: this.currentBreakpoint,
            maxWidth: this.maxWidth,
            fluid: this.fluid,
            centered: this.centered
        };
    },
    
    /**
     * Check if container is visible in viewport
     */
    isVisible() {
        const rect = this.$el.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    },
    
    /**
     * Scroll to container
     */
    scrollIntoView(options = { behavior: 'smooth', block: 'start' }) {
        this.$el.scrollIntoView(options);
    },
    
    /**
     * Debounce utility function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});