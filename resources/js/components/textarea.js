// DSUI Textarea Component
DS.component.textarea = (config = {}) => ({
    ...DS.component.base(config),
    
    // Textarea-specific properties
    value: config.value || '',
    focused: false,
    isDirty: false,
    isValid: true,
    validationMessage: '',
    characterCount: 0,
    autoResize: config.autoResize !== false, // Default to true
    
    init() {
        // Call base component initialization
        DS.component.base(config).init.call(this);
        
        // Textarea-specific initialization
        console.log('DSUI: Textarea component initialized', this.$el);
        
        // Set initial value and character count
        this.value = this.$el.value || '';
        this.updateCharacterCount();
        
        // Auto-resize setup
        if (this.autoResize) {
            this.setupAutoResize();
        }
        
        // Watch for value changes
        this.$watch('value', (newValue) => {
            if (this.$el.value !== newValue) {
                this.$el.value = newValue;
            }
            this.isDirty = true;
            this.updateCharacterCount();
            this.validateTextarea();
            
            if (this.autoResize) {
                this.resize();
            }
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
            this.$dispatch('ds-textarea-focus', { element: this.$el, value: this.value });
        });
        
        this.$el.addEventListener('blur', () => {
            this.focused = false;
            this.validateTextarea();
            this.$dispatch('ds-textarea-blur', { element: this.$el, value: this.value });
        });
        
        this.$el.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        // Custom validation rules from config
        if (config.validationRules) {
            this.validationRules = config.validationRules;
        }
        
        // Initial resize if auto-resize is enabled
        if (this.autoResize) {
            this.$nextTick(() => this.resize());
        }
    },
    
    setupAutoResize() {
        // Set up auto-resize functionality
        this.$el.style.resize = 'none';
        this.$el.style.overflow = 'hidden';
        
        // Add resize observer for better performance
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(() => {
                this.resize();
            });
            this.resizeObserver.observe(this.$el);
        }
    },
    
    resize() {
        if (!this.autoResize) return;
        
        // Reset height to auto to get the correct scrollHeight
        this.$el.style.height = 'auto';
        
        // Calculate the new height
        const minHeight = parseInt(getComputedStyle(this.$el).lineHeight) * (this.$el.rows || 4);
        const newHeight = Math.max(this.$el.scrollHeight, minHeight);
        
        // Set the new height
        this.$el.style.height = newHeight + 'px';
        
        this.$dispatch('ds-textarea-resize', { 
            element: this.$el, 
            height: newHeight 
        });
    },
    
    updateCharacterCount() {
        this.characterCount = this.value.length;
        
        // Update counter display if it exists
        const counterCurrent = document.querySelector(`#${this.$el.id} ~ .ds-textarea__footer .ds-counter-current`);
        if (counterCurrent) {
            counterCurrent.textContent = this.characterCount;
        }
        
        // Update counter color based on limit
        const maxLength = this.$el.maxLength;
        if (maxLength && this.characterCount > maxLength * 0.9) {
            const counter = document.querySelector(`#${this.$el.id} ~ .ds-textarea__footer .ds-textarea__counter`);
            if (counter) {
                if (this.characterCount > maxLength) {
                    counter.classList.add('is-danger');
                    counter.classList.remove('is-warning');
                } else {
                    counter.classList.add('is-warning');
                    counter.classList.remove('is-danger');
                }
            }
        }
    },
    
    validateTextarea() {
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
        
        this.$dispatch('ds-textarea-validated', {
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
        this.$dispatch('ds-textarea-keydown', {
            key: e.key,
            element: this.$el,
            value: this.value
        });
        
        // Handle specific key combinations
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey) && config.onSubmit) {
            e.preventDefault();
            config.onSubmit(this.value, e);
        }
        
        if (e.key === 'Escape' && config.onEscape) {
            config.onEscape(this.value, e);
        }
        
        // Tab key handling for indentation (optional)
        if (e.key === 'Tab' && config.allowTabIndent) {
            e.preventDefault();
            const start = this.$el.selectionStart;
            const end = this.$el.selectionEnd;
            
            // Insert tab at cursor position
            const value = this.$el.value;
            this.$el.value = value.substring(0, start) + '\t' + value.substring(end);
            this.$el.selectionStart = this.$el.selectionEnd = start + 1;
            
            // Trigger input event to update Alpine state
            this.$el.dispatchEvent(new Event('input'));
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
        if (this.autoResize) {
            this.resize();
        }
    },
    
    setValue(newValue) {
        this.value = newValue;
    },
    
    getValue() {
        return this.value;
    },
    
    validate() {
        this.isDirty = true;
        this.validateTextarea();
        return this.isValid;
    },
    
    insertAtCursor(text) {
        const start = this.$el.selectionStart;
        const end = this.$el.selectionEnd;
        const value = this.$el.value;
        
        this.$el.value = value.substring(0, start) + text + value.substring(end);
        this.$el.selectionStart = this.$el.selectionEnd = start + text.length;
        
        // Trigger input event to update Alpine state
        this.$el.dispatchEvent(new Event('input'));
    }
});