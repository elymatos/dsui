/**
 * DSUI Checkbox Component
 * Alpine.js component for enhanced checkbox functionality with indeterminate state and HTMX integration
 */

DS.component.checkbox = (config = {}) => ({
    // Component state
    checked: config.checked || false,
    indeterminate: config.indeterminate || false,
    value: config.value || true,
    disabled: config.disabled || false,
    loading: config.loading || false,
    name: config.name || '',
    
    // Internal state
    focused: false,
    
    /**
     * Initialize the component
     */
    init() {
        // Set initial indeterminate state on the DOM element
        this.updateIndeterminate();
        
        // Watch for indeterminate changes
        this.$watch('indeterminate', () => {
            this.updateIndeterminate();
            this.onStateChange();
        });
        
        // Watch for checked changes
        this.$watch('checked', () => {
            // If checked is set, clear indeterminate state
            if (this.checked) {
                this.indeterminate = false;
            }
            this.onStateChange();
        });
        
        // Setup keyboard navigation
        this.setupKeyboardHandling();
        
        // Setup focus management
        this.setupFocusHandling();
    },
    
    /**
     * Handle checkbox change
     */
    handleChange() {
        // Clear indeterminate state when user interacts
        if (this.indeterminate) {
            this.indeterminate = false;
        }
        
        this.onStateChange();
    },
    
    /**
     * Toggle checkbox state
     */
    toggle() {
        if (this.disabled) return;
        
        if (this.indeterminate) {
            this.indeterminate = false;
            this.checked = true;
        } else {
            this.checked = !this.checked;
        }
    },
    
    /**
     * Set checked state
     */
    setChecked(state) {
        this.checked = state;
        if (state) {
            this.indeterminate = false;
        }
    },
    
    /**
     * Set indeterminate state
     */
    setIndeterminate(state) {
        this.indeterminate = state;
        if (state) {
            this.checked = false;
        }
    },
    
    /**
     * Update indeterminate property on DOM element
     */
    updateIndeterminate() {
        if (this.$refs.checkbox) {
            this.$refs.checkbox.indeterminate = this.indeterminate;
        }
    },
    
    /**
     * Get ARIA checked value
     */
    getAriaChecked() {
        if (this.indeterminate) return 'mixed';
        return this.checked ? 'true' : 'false';
    },
    
    /**
     * Setup keyboard handling
     */
    setupKeyboardHandling() {
        // Space key handling is automatic with checkbox input
        // Add any custom keyboard handling here if needed
    },
    
    /**
     * Setup focus handling
     */
    setupFocusHandling() {
        this.$el.addEventListener('focusin', () => {
            this.focused = true;
        });
        
        this.$el.addEventListener('focusout', () => {
            this.focused = false;
        });
    },
    
    /**
     * Handle state change
     */
    onStateChange() {
        // Emit custom event for external listeners
        this.$dispatch('checkbox-change', {
            checked: this.checked,
            indeterminate: this.indeterminate,
            value: this.getValue(),
            name: this.name
        });
        
        // HTMX integration
        if (config.htmxAction) {
            this.$dispatch('htmx-trigger', {
                action: config.htmxAction,
                data: { 
                    checked: this.checked,
                    indeterminate: this.indeterminate,
                    value: this.getValue()
                }
            });
        }
        
        // Form integration
        this.updateFormValue();
    },
    
    /**
     * Get current value for form submission
     */
    getValue() {
        if (this.indeterminate) {
            return null; // Or some specific indeterminate value
        }
        return this.checked ? this.value : null;
    },
    
    /**
     * Update hidden form input if exists
     */
    updateFormValue() {
        const hiddenInput = this.$el.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            hiddenInput.value = this.getValue() || '';
        }
    },
    
    /**
     * Check if checkbox is in a valid state
     */
    isValid() {
        // Add custom validation logic here
        return true;
    },
    
    /**
     * Reset to initial state
     */
    reset() {
        this.checked = config.checked || false;
        this.indeterminate = config.indeterminate || false;
    },
    
    /**
     * Programmatically focus the checkbox
     */
    focus() {
        if (this.$refs.checkbox) {
            this.$refs.checkbox.focus();
        }
    },
    
    /**
     * Programmatically blur the checkbox
     */
    blur() {
        if (this.$refs.checkbox) {
            this.$refs.checkbox.blur();
        }
    }
});