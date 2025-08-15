// DSUI Button Component
DS.component.button = (config = {}) => ({
    ...DS.component.base(config),
    
    // Button-specific properties
    clicked: false,
    clickCount: 0,
    
    init() {
        // Call base component initialization
        DS.component.base(config).init.call(this);
        
        // Button-specific initialization
        console.log('DSUI: Button component initialized', this.$el);
        
        // Add click tracking
        this.$watch('clicked', (value) => {
            if (value) {
                this.clickCount++;
                // Reset clicked state after animation
                setTimeout(() => {
                    this.clicked = false;
                }, 200);
            }
        });
        
        // Handle keyboard navigation
        this.$el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.handleClick();
            }
        });
    },
    
    async handleClick(event = null) {
        if (this.disabled || this.loading) {
            if (event) event.preventDefault();
            return false;
        }
        
        this.clicked = true;
        this.loading = true;
        
        try {
            // Emit click event for parent components
            this.$dispatch('ds-button-click', {
                element: this.$el,
                variant: this.variant,
                clickCount: this.clickCount,
                timestamp: Date.now()
            });
            
            // Custom click handling from config
            if (config.onClick && typeof config.onClick === 'function') {
                await config.onClick(event);
            }
            
            // Handle HTMX forms
            if (this.$el.form && this.$el.type === 'submit') {
                // Let HTMX handle form submission
                return true;
            }
            
            // Handle navigation for link buttons
            if (this.$el.tagName.toLowerCase() === 'a' && this.$el.href) {
                if (!event || (!event.ctrlKey && !event.metaKey)) {
                    // Allow normal navigation unless modified click
                    return true;
                }
            }
            
        } catch (error) {
            console.error('DSUI: Button click error', error);
            
            this.$dispatch('ds-button-error', {
                error: error.message,
                element: this.$el
            });
            
        } finally {
            // Reset loading state after minimum duration for UX
            setTimeout(() => {
                this.loading = false;
            }, config.minLoadingDuration || 300);
        }
    },
    
    // Public methods
    click() {
        return this.handleClick();
    },
    
    focus() {
        this.$el.focus();
    },
    
    blur() {
        this.$el.blur();
    }
});

// Register button-specific Alpine directives
document.addEventListener('alpine:init', () => {
    Alpine.directive('ds-button-click', (el, { expression }, { evaluate }) => {
        el.addEventListener('click', (e) => {
            // Evaluate the expression in the Alpine context
            evaluate(expression);
        });
    });
});