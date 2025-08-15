/**
 * DSUI Select Component
 * Alpine.js component for advanced select functionality with search, multi-select, and HTMX integration
 */

DS.component.select = (config = {}) => ({
    // Component state
    options: config.options || [],
    multiple: config.multiple || false,
    searchable: config.searchable || false,
    clearable: config.clearable || false,
    placeholder: config.placeholder || 'Select an option...',
    searchPlaceholder: config.searchPlaceholder || 'Search options...',
    maxHeight: config.maxHeight || 200,
    disabled: config.disabled || false,
    loading: config.loading || false,
    
    // Internal state
    isOpen: false,
    searchTerm: '',
    highlightedIndex: -1,
    selectedValues: [],
    
    /**
     * Initialize the component
     */
    init() {
        // Set initial selected values
        if (config.value !== undefined && config.value !== null) {
            if (this.multiple) {
                this.selectedValues = Array.isArray(config.value) ? [...config.value] : [config.value];
            } else {
                this.selectedValues = [config.value];
            }
        }
        
        // Watch for external value changes
        this.$watch('selectedValues', () => {
            this.onSelectionChange();
        });
        
        // Close dropdown when clicking outside
        this.$watch('isOpen', (isOpen) => {
            if (isOpen) {
                this.$nextTick(() => {
                    if (this.searchable && this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            } else {
                this.searchTerm = '';
                this.highlightedIndex = -1;
            }
        });
        
        // Handle keyboard navigation
        this.setupKeyboardNavigation();
    },
    
    /**
     * Get filtered options based on search term
     */
    get filteredOptions() {
        if (!this.searchable || !this.searchTerm.trim()) {
            return this.options;
        }
        
        const searchTerm = this.searchTerm.toLowerCase();
        return this.options.filter(option => 
            option.label.toLowerCase().includes(searchTerm)
        );
    },
    
    /**
     * Toggle dropdown open/closed
     */
    toggle() {
        if (this.disabled) return;
        this.isOpen ? this.close() : this.open();
    },
    
    /**
     * Open the dropdown
     */
    open() {
        if (this.disabled) return;
        this.isOpen = true;
        this.highlightedIndex = this.getSelectedIndex();
    },
    
    /**
     * Close the dropdown
     */
    close() {
        this.isOpen = false;
    },
    
    /**
     * Select an option
     */
    selectOption(option) {
        if (option.disabled) return;
        
        if (this.multiple) {
            const index = this.selectedValues.indexOf(option.value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            } else {
                this.selectedValues.push(option.value);
            }
        } else {
            this.selectedValues = [option.value];
            this.close();
        }
        
        this.highlightedIndex = this.filteredOptions.findIndex(opt => opt.value === option.value);
    },
    
    /**
     * Clear all selections
     */
    clear() {
        this.selectedValues = [];
        this.close();
    },
    
    /**
     * Check if an option is selected
     */
    isOptionSelected(value) {
        return this.selectedValues.includes(value);
    },
    
    /**
     * Check if there's any selection
     */
    hasSelection() {
        return this.selectedValues.length > 0;
    },
    
    /**
     * Get display text for the trigger
     */
    getDisplayText() {
        if (!this.hasSelection()) {
            return this.placeholder;
        }
        
        if (!this.multiple) {
            const option = this.options.find(opt => opt.value === this.selectedValues[0]);
            return option ? option.label : this.placeholder;
        }
        
        const selectedOptions = this.options.filter(opt => 
            this.selectedValues.includes(opt.value)
        );
        
        if (selectedOptions.length === 0) {
            return this.placeholder;
        }
        
        if (selectedOptions.length === 1) {
            return selectedOptions[0].label;
        }
        
        if (selectedOptions.length <= 2) {
            return selectedOptions.map(opt => opt.label).join(', ');
        }
        
        return `${selectedOptions.length} options selected`;
    },
    
    /**
     * Get form value for submission
     */
    getFormValue() {
        if (!this.hasSelection()) {
            return this.multiple ? [] : '';
        }
        
        return this.multiple ? this.selectedValues : this.selectedValues[0];
    },
    
    /**
     * Highlight next option
     */
    highlightNext() {
        if (this.filteredOptions.length === 0) return;
        
        this.highlightedIndex = Math.min(
            this.highlightedIndex + 1,
            this.filteredOptions.length - 1
        );
    },
    
    /**
     * Highlight previous option
     */
    highlightPrevious() {
        this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
    },
    
    /**
     * Select highlighted option
     */
    selectHighlighted() {
        if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
            this.selectOption(this.filteredOptions[this.highlightedIndex]);
        }
    },
    
    /**
     * Get index of selected option
     */
    getSelectedIndex() {
        if (!this.hasSelection() || this.multiple) return 0;
        
        return this.filteredOptions.findIndex(opt => 
            opt.value === this.selectedValues[0]
        );
    },
    
    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        // Additional keyboard handling can be added here
    },
    
    /**
     * Handle selection change
     */
    onSelectionChange() {
        // Emit custom event for external listeners
        this.$dispatch('select-change', {
            value: this.getFormValue(),
            selectedOptions: this.options.filter(opt => 
                this.selectedValues.includes(opt.value)
            )
        });
        
        // HTMX integration
        if (config.htmxAction) {
            this.$dispatch('htmx-trigger', {
                action: config.htmxAction,
                data: { value: this.getFormValue() }
            });
        }
    },
    
    /**
     * Update options externally
     */
    updateOptions(newOptions) {
        this.options = newOptions;
        // Clear invalid selections
        this.selectedValues = this.selectedValues.filter(value =>
            newOptions.some(opt => opt.value === value)
        );
    },
    
    /**
     * Set value externally
     */
    setValue(value) {
        if (this.multiple) {
            this.selectedValues = Array.isArray(value) ? [...value] : [value];
        } else {
            this.selectedValues = value ? [value] : [];
        }
    },
    
    /**
     * Get current value
     */
    getValue() {
        return this.getFormValue();
    }
});