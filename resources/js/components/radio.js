/**
 * DSUI Radio Component
 * Alpine.js component for radio button groups with enhanced functionality and HTMX integration
 */

DS.component.radio = (config = {}) => ({
    // Component state
    selectedValue: config.selectedValue || null,
    name: config.name || '',
    disabled: config.disabled || false,
    loading: config.loading || false,
    options: config.options || [],
    
    // Internal state
    focused: false,
    focusedIndex: -1,
    
    /**
     * Initialize the component
     */
    init() {
        // Watch for selected value changes
        this.$watch('selectedValue', (value) => {
            this.onSelectionChange(value);
        });
        
        // Setup keyboard navigation
        this.setupKeyboardNavigation();
        
        // Setup focus management
        this.setupFocusManagement();
        
        // Initialize focus index if we have a selected value
        if (this.selectedValue !== null) {
            this.focusedIndex = this.options.findIndex(option => option.value == this.selectedValue);
        }
    },
    
    /**
     * Handle radio button change
     */
    handleChange(value) {
        if (this.disabled) return;
        
        this.selectedValue = value;
        this.focusedIndex = this.options.findIndex(option => option.value == value);
    },
    
    /**
     * Select a radio option by value
     */
    selectValue(value) {
        if (this.disabled) return;
        
        const option = this.options.find(opt => opt.value == value);
        if (option && !option.disabled) {
            this.selectedValue = value;
        }
    },
    
    /**
     * Select a radio option by index
     */
    selectByIndex(index) {
        if (this.disabled || index < 0 || index >= this.options.length) return;
        
        const option = this.options[index];
        if (option && !option.disabled) {
            this.selectedValue = option.value;
            this.focusedIndex = index;
        }
    },
    
    /**
     * Clear selection
     */
    clearSelection() {
        if (this.disabled) return;
        this.selectedValue = null;
        this.focusedIndex = -1;
    },
    
    /**
     * Check if a value is selected
     */
    isSelected(value) {
        return this.selectedValue == value;
    },
    
    /**
     * Get the selected option object
     */
    getSelectedOption() {
        if (this.selectedValue === null) return null;
        return this.options.find(option => option.value == this.selectedValue) || null;
    },
    
    /**
     * Setup keyboard navigation (arrow keys)
     */
    setupKeyboardNavigation() {
        this.$el.addEventListener('keydown', (event) => {
            if (this.disabled) return;
            
            switch (event.key) {
                case 'ArrowDown':
                case 'ArrowRight':
                    event.preventDefault();
                    this.selectNext();
                    break;
                    
                case 'ArrowUp':
                case 'ArrowLeft':
                    event.preventDefault();
                    this.selectPrevious();
                    break;
                    
                case 'Home':
                    event.preventDefault();
                    this.selectFirst();
                    break;
                    
                case 'End':
                    event.preventDefault();
                    this.selectLast();
                    break;
            }
        });
    },
    
    /**
     * Setup focus management
     */
    setupFocusManagement() {
        this.$el.addEventListener('focusin', () => {
            this.focused = true;
        });
        
        this.$el.addEventListener('focusout', () => {
            this.focused = false;
        });
    },
    
    /**
     * Select next enabled option
     */
    selectNext() {
        const enabledOptions = this.options.filter(option => !option.disabled);
        if (enabledOptions.length === 0) return;
        
        let currentIndex = this.selectedValue !== null 
            ? enabledOptions.findIndex(option => option.value == this.selectedValue)
            : -1;
            
        const nextIndex = (currentIndex + 1) % enabledOptions.length;
        this.selectValue(enabledOptions[nextIndex].value);
        this.focusOption(enabledOptions[nextIndex].value);
    },
    
    /**
     * Select previous enabled option
     */
    selectPrevious() {
        const enabledOptions = this.options.filter(option => !option.disabled);
        if (enabledOptions.length === 0) return;
        
        let currentIndex = this.selectedValue !== null 
            ? enabledOptions.findIndex(option => option.value == this.selectedValue)
            : 0;
            
        const prevIndex = currentIndex <= 0 ? enabledOptions.length - 1 : currentIndex - 1;
        this.selectValue(enabledOptions[prevIndex].value);
        this.focusOption(enabledOptions[prevIndex].value);
    },
    
    /**
     * Select first enabled option
     */
    selectFirst() {
        const firstEnabled = this.options.find(option => !option.disabled);
        if (firstEnabled) {
            this.selectValue(firstEnabled.value);
            this.focusOption(firstEnabled.value);
        }
    },
    
    /**
     * Select last enabled option
     */
    selectLast() {
        const enabledOptions = this.options.filter(option => !option.disabled);
        const lastEnabled = enabledOptions[enabledOptions.length - 1];
        if (lastEnabled) {
            this.selectValue(lastEnabled.value);
            this.focusOption(lastEnabled.value);
        }
    },
    
    /**
     * Focus a specific radio option
     */
    focusOption(value) {
        const input = this.$el.querySelector(`input[value="${value}"]`);
        if (input) {
            input.focus();
        }
    },
    
    /**
     * Handle selection change
     */
    onSelectionChange(value) {
        // Emit custom event for external listeners
        this.$dispatch('radio-change', {
            value: value,
            selectedOption: this.getSelectedOption(),
            name: this.name
        });
        
        // HTMX integration
        if (config.htmxAction) {
            this.$dispatch('htmx-trigger', {
                action: config.htmxAction,
                data: { 
                    value: value,
                    name: this.name
                }
            });
        }
        
        // Form integration
        this.updateFormValue();
    },
    
    /**
     * Update hidden form input if exists
     */
    updateFormValue() {
        const hiddenInput = this.$el.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            hiddenInput.value = this.selectedValue || '';
        }
    },
    
    /**
     * Get current value for form submission
     */
    getValue() {
        return this.selectedValue;
    },
    
    /**
     * Set value externally
     */
    setValue(value) {
        this.selectedValue = value;
    },
    
    /**
     * Check if radio group is in a valid state
     */
    isValid() {
        // Add custom validation logic here
        return true;
    },
    
    /**
     * Reset to initial state
     */
    reset() {
        this.selectedValue = config.selectedValue || null;
        this.focusedIndex = -1;
    },
    
    /**
     * Enable/disable the radio group
     */
    setDisabled(state) {
        this.disabled = state;
    },
    
    /**
     * Get all available values
     */
    getAvailableValues() {
        return this.options.filter(option => !option.disabled).map(option => option.value);
    },
    
    /**
     * Get option by value
     */
    getOptionByValue(value) {
        return this.options.find(option => option.value == value) || null;
    }
});