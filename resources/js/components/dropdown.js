// DSUI Enhanced Dropdown Component
DS.component.dropdown = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core data
    options: config.options || [],
    originalOptions: [],
    visibleOptions: [],
    selectedValues: [],
    selectedOptions: [],
    
    // State
    open: false,
    searchQuery: '',
    focusedIndex: -1,
    searching: false,
    
    // Configuration
    multiple: config.multiple || false,
    searchable: config.searchable || false,
    searchPlaceholder: config.searchPlaceholder || 'Search options...',
    clearable: config.clearable !== undefined ? config.clearable : true,
    placeholder: config.placeholder || 'Select an option...',
    creatable: config.creatable || false,
    maxItems: config.maxItems || 0,
    grouping: config.grouping || false,
    tagging: config.tagging || false,
    position: config.position || 'bottom',
    virtualized: config.virtualized || false,
    virtualItemHeight: config.virtualItemHeight || 32,
    asyncSearch: config.asyncSearch || false,
    searchUrl: config.searchUrl || null,
    minSearchLength: config.minSearchLength || 2,
    searchDelay: config.searchDelay || 300,
    closeOnSelect: config.closeOnSelect !== undefined ? config.closeOnSelect : true,
    
    // Internal state
    dropdownId: 'dropdown-' + Math.random().toString(36).substr(2, 9),
    searchTimeout: null,
    actualPosition: 'bottom',
    maxTagsToShow: 3,
    virtualHeight: 200,
    virtualStartIndex: 0,
    virtualEndIndex: 10,
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Initialize data
        this.originalOptions = [...this.options];
        this.processOptions();
        this.initializeValue();
        
        // Setup position detection
        this.updatePosition();
        
        // Setup virtual scrolling
        if (this.virtualized) {
            this.setupVirtualization();
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.$el.contains(e.target)) {
                this.close();
            }
        });
        
        // Handle window resize for position updates
        window.addEventListener('resize', () => {
            if (this.open) {
                this.updatePosition();
            }
        });
        
        console.log('DSUI: Dropdown initialized', {
            options: this.options.length,
            multiple: this.multiple,
            searchable: this.searchable
        });
    },
    
    // Computed properties
    get hasValue() {
        return this.selectedValues.length > 0;
    },
    
    get hasExactMatch() {
        return this.visibleOptions.some(option => 
            option.label.toLowerCase() === this.searchQuery.toLowerCase()
        );
    },
    
    get canCreateNew() {
        return this.searchQuery.length >= this.minSearchLength;
    },
    
    // Data processing
    processOptions() {
        let processed = [...this.originalOptions];
        
        // Add group headers if grouping is enabled
        if (this.grouping) {
            processed = this.addGroupHeaders(processed);
        }
        
        this.visibleOptions = processed;
        this.updateVirtualization();
    },
    
    addGroupHeaders(options) {
        const grouped = {};
        const ungrouped = [];
        
        // Group options
        options.forEach(option => {
            if (option.group) {
                if (!grouped[option.group]) {
                    grouped[option.group] = [];
                }
                grouped[option.group].push(option);
            } else {
                ungrouped.push(option);
            }
        });
        
        // Build final list with headers
        const result = [];
        
        // Add ungrouped options first
        if (ungrouped.length > 0) {
            result.push(...ungrouped);
        }
        
        // Add grouped options with headers
        Object.keys(grouped).sort().forEach(groupName => {
            result.push({
                value: `group-${groupName}`,
                label: groupName,
                isGroupHeader: true,
                disabled: true
            });
            result.push(...grouped[groupName]);
        });
        
        return result;
    },
    
    initializeValue() {
        const initialValue = config.value;
        
        if (initialValue !== null && initialValue !== undefined) {
            if (this.multiple) {
                this.selectedValues = Array.isArray(initialValue) ? initialValue : [initialValue];
            } else {
                this.selectedValues = [initialValue];
            }
        }
        
        this.updateSelectedOptions();
    },
    
    updateSelectedOptions() {
        this.selectedOptions = this.selectedValues.map(value => 
            this.originalOptions.find(option => option.value === value)
        ).filter(Boolean);
        
        // Emit change event
        this.$dispatch('ds-dropdown-change', {
            values: this.selectedValues,
            options: this.selectedOptions,
            multiple: this.multiple
        });
    },
    
    // Dropdown control
    toggle() {
        if (this.disabled) return;
        
        if (this.open) {
            this.close();
        } else {
            this.open();
        }
    },
    
    open() {
        if (this.disabled) return;
        
        this.open = true;
        this.updatePosition();
        
        // Focus search input if searchable
        if (this.searchable) {
            this.$nextTick(() => {
                const searchInput = this.$refs.searchInput;
                if (searchInput) {
                    searchInput.focus();
                }
            });
        }
        
        // Reset search
        this.searchQuery = '';
        this.processOptions();
        this.focusedIndex = -1;
        
        this.$dispatch('ds-dropdown-open');
    },
    
    close() {
        this.open = false;
        this.searchQuery = '';
        this.focusedIndex = -1;
        this.searching = false;
        
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        this.$dispatch('ds-dropdown-close');
    },
    
    // Option selection
    selectOption(option) {
        if (option.disabled || option.isGroupHeader) return;
        
        if (this.multiple) {
            if (this.isSelected(option.value)) {
                this.removeOption(option.value);
            } else {
                this.addOption(option.value);
            }
        } else {
            this.selectedValues = [option.value];
            this.updateSelectedOptions();
            
            if (this.closeOnSelect) {
                this.close();
            }
        }
    },
    
    addOption(value) {
        if (this.maxItems > 0 && this.selectedValues.length >= this.maxItems) {
            return;
        }
        
        if (!this.selectedValues.includes(value)) {
            this.selectedValues.push(value);
            this.updateSelectedOptions();
        }
    },
    
    removeOption(value) {
        this.selectedValues = this.selectedValues.filter(v => v !== value);
        this.updateSelectedOptions();
    },
    
    clearSelection() {
        this.selectedValues = [];
        this.selectedOptions = [];
        this.updateSelectedOptions();
        
        this.$dispatch('ds-dropdown-clear');
    },
    
    createNewOption() {
        if (!this.canCreateNew || this.hasExactMatch) return;
        
        const newOption = {
            value: this.searchQuery,
            label: this.searchQuery,
            description: null,
            icon: null,
            avatar: null,
            disabled: false,
            group: null,
            data: { created: true }
        };
        
        // Add to options
        this.originalOptions.push(newOption);
        this.options.push(newOption);
        
        // Select the new option
        this.selectOption(newOption);
        
        // Clear search
        this.searchQuery = '';
        this.processOptions();
        
        this.$dispatch('ds-dropdown-create', { option: newOption });
    },
    
    isSelected(value) {
        return this.selectedValues.includes(value);
    },
    
    getOptionIndex(option) {
        return this.visibleOptions.findIndex(opt => opt.value === option.value);
    },
    
    // Search functionality
    handleSearch() {
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        this.searchTimeout = setTimeout(() => {
            this.performSearch();
        }, this.searchDelay);
    },
    
    async performSearch() {
        if (this.asyncSearch && this.searchQuery.length >= this.minSearchLength) {
            await this.performAsyncSearch();
        } else {
            this.performLocalSearch();
        }
    },
    
    performLocalSearch() {
        if (!this.searchQuery) {
            this.visibleOptions = [...this.originalOptions];
        } else {
            const query = this.searchQuery.toLowerCase();
            this.visibleOptions = this.originalOptions.filter(option => 
                !option.isGroupHeader && (
                    option.label.toLowerCase().includes(query) ||
                    (option.description && option.description.toLowerCase().includes(query))
                )
            );
        }
        
        // Re-add group headers if grouping is enabled
        if (this.grouping) {
            this.visibleOptions = this.addGroupHeaders(this.visibleOptions);
        }
        
        this.updateVirtualization();
        this.focusedIndex = -1;
        
        this.$dispatch('ds-dropdown-search', {
            query: this.searchQuery,
            results: this.visibleOptions.length
        });
    },
    
    async performAsyncSearch() {
        if (!this.searchUrl) return;
        
        this.searching = true;
        
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.searchQuery)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.visibleOptions = data.options || [];
            
            this.updateVirtualization();
            this.focusedIndex = -1;
            
            this.$dispatch('ds-dropdown-search', {
                query: this.searchQuery,
                results: this.visibleOptions.length,
                async: true
            });
            
        } catch (error) {
            console.error('DSUI: Async search failed', error);
            this.visibleOptions = [];
            
            this.$dispatch('ds-dropdown-search-error', {
                query: this.searchQuery,
                error: error
            });
        } finally {
            this.searching = false;
        }
    },
    
    highlightMatch(text) {
        if (!this.searchQuery) return text;
        
        const regex = new RegExp(`(${this.searchQuery})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    },
    
    // Keyboard navigation
    handleTriggerKeydown(event) {
        switch (event.key) {
            case 'Enter':
            case ' ':
                event.preventDefault();
                this.toggle();
                break;
                
            case 'ArrowDown':
                event.preventDefault();
                if (!this.open) {
                    this.open();
                } else {
                    this.focusNext();
                }
                break;
                
            case 'ArrowUp':
                event.preventDefault();
                if (this.open) {
                    this.focusPrevious();
                }
                break;
                
            case 'Escape':
                this.close();
                break;
        }
    },
    
    handleSearchKeydown(event) {
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.focusNext();
                break;
                
            case 'ArrowUp':
                event.preventDefault();
                this.focusPrevious();
                break;
                
            case 'Enter':
                event.preventDefault();
                if (this.focusedIndex >= 0) {
                    const option = this.visibleOptions[this.focusedIndex];
                    if (option && !option.disabled && !option.isGroupHeader) {
                        this.selectOption(option);
                    }
                } else if (this.creatable && this.canCreateNew) {
                    this.createNewOption();
                }
                break;
                
            case 'Escape':
                this.close();
                break;
                
            case 'Tab':
                this.close();
                break;
        }
    },
    
    focusNext() {
        const selectableOptions = this.visibleOptions.filter(opt => !opt.disabled && !opt.isGroupHeader);
        if (selectableOptions.length === 0) return;
        
        this.focusedIndex = Math.min(this.focusedIndex + 1, selectableOptions.length - 1);
        this.scrollToFocused();
    },
    
    focusPrevious() {
        const selectableOptions = this.visibleOptions.filter(opt => !opt.disabled && !opt.isGroupHeader);
        if (selectableOptions.length === 0) return;
        
        this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
        this.scrollToFocused();
    },
    
    setFocusedIndex(index) {
        this.focusedIndex = index;
    },
    
    scrollToFocused() {
        this.$nextTick(() => {
            const focusedOption = this.$el.querySelector('.ds-dropdown-option--focused');
            if (focusedOption) {
                focusedOption.scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }
        });
    },
    
    // Position management
    updatePosition() {
        this.$nextTick(() => {
            if (!this.open) return;
            
            const trigger = this.$el.querySelector('.ds-dropdown-trigger');
            const menu = this.$el.querySelector('.ds-dropdown-menu');
            
            if (!trigger || !menu) return;
            
            const triggerRect = trigger.getBoundingClientRect();
            const menuHeight = menu.offsetHeight || 200;
            const viewportHeight = window.innerHeight;
            
            const spaceBelow = viewportHeight - triggerRect.bottom;
            const spaceAbove = triggerRect.top;
            
            if (this.position === 'auto') {
                this.actualPosition = spaceBelow >= menuHeight || spaceBelow >= spaceAbove ? 'bottom' : 'top';
            } else {
                this.actualPosition = this.position;
            }
        });
    },
    
    // Virtualization
    setupVirtualization() {
        if (!this.virtualized) return;
        
        const optionsContainer = this.$el.querySelector('.ds-dropdown-options');
        if (!optionsContainer) return;
        
        optionsContainer.addEventListener('scroll', () => {
            this.updateVirtualization();
        });
    },
    
    updateVirtualization() {
        if (!this.virtualized) return;
        
        const containerHeight = this.virtualHeight;
        const itemHeight = this.virtualItemHeight;
        const visibleCount = Math.ceil(containerHeight / itemHeight);
        const scrollTop = this.$el.querySelector('.ds-dropdown-options')?.scrollTop || 0;
        
        this.virtualStartIndex = Math.floor(scrollTop / itemHeight);
        this.virtualEndIndex = Math.min(
            this.visibleOptions.length,
            this.virtualStartIndex + visibleCount + 5
        );
    },
    
    // Utility methods
    getAriaLabel() {
        if (this.selectedOptions.length === 0) {
            return this.placeholder;
        }
        
        if (this.multiple) {
            return `${this.selectedOptions.length} options selected`;
        }
        
        return this.selectedOptions[0]?.label || '';
    },
    
    // Public API
    selectValue(value) {
        if (this.multiple) {
            this.addOption(value);
        } else {
            this.selectedValues = [value];
            this.updateSelectedOptions();
        }
    },
    
    deselectValue(value) {
        this.removeOption(value);
    },
    
    clear() {
        this.clearSelection();
    },
    
    addOptions(newOptions) {
        this.originalOptions.push(...newOptions);
        this.options.push(...newOptions);
        this.processOptions();
    },
    
    removeOptions(valuesToRemove) {
        this.originalOptions = this.originalOptions.filter(opt => !valuesToRemove.includes(opt.value));
        this.options = this.options.filter(opt => !valuesToRemove.includes(opt.value));
        this.selectedValues = this.selectedValues.filter(val => !valuesToRemove.includes(val));
        this.processOptions();
        this.updateSelectedOptions();
    },
    
    updateOptions(newOptions) {
        this.originalOptions = newOptions;
        this.options = newOptions;
        this.processOptions();
        this.updateSelectedOptions();
    }
});