// DSUI Input Component
DS.component.input = (config = {}) => ({
    ...DS.component.base(config),
    
    // Input-specific properties
    value: config.value || '',
    focused: false,
    isDirty: false,
    isValid: true,
    validationMessage: '',
    characterCount: 0,
    
    init() {
        // Call base component initialization
        DS.component.base(config).init.call(this);
        
        // Input-specific initialization
        console.log('DSUI: Input component initialized', this.$el);
        
        // Set initial value and character count
        this.value = this.$el.value || '';
        this.updateCharacterCount();
        
        // Watch for value changes
        this.$watch('value', (newValue) => {
            if (this.$el.value !== newValue) {
                this.$el.value = newValue;
            }
            this.isDirty = true;
            this.updateCharacterCount();
            this.validateInput();
        });
        
        // Watch for focus state
        this.$watch('focused', (focused) => {
            if (focused) {
                this.$el.classList.add('is-focused');
            } else {
                this.$el.classList.remove('is-focused');
            }
        });
        
        // Watch for validation state
        this.$watch('isValid', (valid) => {
            if (valid) {
                this.$el.classList.remove('is-danger');
                this.$el.classList.add('is-success');
            } else {
                this.$el.classList.remove('is-success');
                this.$el.classList.add('is-danger');
            }
        });
        
        // Setup event listeners
        this.$el.addEventListener('input', (e) => {
            this.value = e.target.value;
        });
        
        this.$el.addEventListener('focus', () => {
            this.focused = true;
            this.$dispatch('ds-input-focus', { element: this.$el, value: this.value });
        });
        
        this.$el.addEventListener('blur', () => {
            this.focused = false;
            this.validateInput();
            this.$dispatch('ds-input-blur', { element: this.$el, value: this.value });
        });
        
        this.$el.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        // Custom validation rules from config
        if (config.validationRules) {
            this.validationRules = config.validationRules;
        }
    },
    
    // Input methods
    updateCharacterCount() {
        this.characterCount = this.value.length;
    },
    
    validateInput() {
        if (!this.isDirty) return;
        
        // Reset validation state
        this.isValid = true;
        this.validationMessage = '';
        
        // Browser validation first
        if (!this.$el.checkValidity()) {
            this.isValid = false;
            this.validationMessage = this.$el.validationMessage;
            this.showValidationError();
            return;
        }
        
        // Custom validation rules
        if (this.validationRules && typeof this.validationRules === 'function') {
            const result = this.validationRules(this.value);
            if (result !== true) {
                this.isValid = false;
                this.validationMessage = result;
                this.showValidationError();
                return;
            }
        }
        
        // Validation passed
        this.hideValidationError();
        
        this.$dispatch('ds-input-validated', {
            element: this.$el,
            value: this.value,
            isValid: this.isValid,
            message: this.validationMessage
        });
    },
    
    showValidationError() {
        const errorElement = document.getElementById(this.$el.id + '_error');
        if (errorElement) {
            errorElement.textContent = this.validationMessage;
            errorElement.style.display = 'block';
        }
    },
    
    hideValidationError() {
        const errorElement = document.getElementById(this.$el.id + '_error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    },
    
    handleKeydown(e) {
        // Emit keydown event for parent components
        this.$dispatch('ds-input-keydown', {
            key: e.key,
            element: this.$el,
            value: this.value
        });
        
        // Handle specific keys
        if (e.key === 'Enter' && config.onEnter) {
            config.onEnter(this.value, e);
        }
        
        if (e.key === 'Escape' && config.onEscape) {
            config.onEscape(this.value, e);
        }
    },
    
    // Public methods
    focus() {
        this.$el.focus();
    },
    
    blur() {
        this.$el.blur();
    },
    
    clear() {
        this.value = '';
        this.isDirty = false;
        this.isValid = true;
        this.validationMessage = '';
    },
    
    setValue(newValue) {
        this.value = newValue;
    },
    
    getValue() {
        return this.value;
    },
    
    validate() {
        this.isDirty = true;
        this.validateInput();
        return this.isValid;
    }
});

// Register input-specific Alpine directives
document.addEventListener('alpine:init', () => {
    Alpine.directive('ds-input-mask', (el, { expression }, { evaluate }) => {
        // Input masking functionality
        const mask = evaluate(expression);
        if (mask) {
            el.addEventListener('input', (e) => {
                // Simple mask implementation
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits for phone mask
                if (mask === 'phone') {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                }
                e.target.value = value;
            });
        }
    });
    
    Alpine.directive('ds-input-autosize', (el) => {
        // Auto-resize functionality for textareas (will be useful later)
        if (el.tagName.toLowerCase() === 'textarea') {
            const resize = () => {
                el.style.height = 'auto';
                el.style.height = el.scrollHeight + 'px';
            };
            
            el.addEventListener('input', resize);
            resize(); // Initial resize
        }
    });
});