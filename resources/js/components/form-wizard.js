// DSUI Form Wizard Component
DS.component.formWizard = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core data
    steps: config.steps || [],
    currentStep: config.currentStep || 0,
    formData: {},
    fieldErrors: {},
    
    // State
    submitted: false,
    
    // Configuration
    showProgress: config.showProgress !== undefined ? config.showProgress : true,
    showStepNumbers: config.showStepNumbers !== undefined ? config.showStepNumbers : true,
    allowStepNavigation: config.allowStepNavigation || false,
    validateOnNext: config.validateOnNext !== undefined ? config.validateOnNext : true,
    saveProgress: config.saveProgress || false,
    progressKey: config.progressKey || 'form_wizard_progress',
    showStepSummary: config.showStepSummary || false,
    submitButtonText: config.submitButtonText || 'Submit',
    nextButtonText: config.nextButtonText || 'Next',
    previousButtonText: config.previousButtonText || 'Previous',
    orientation: config.orientation || 'horizontal',
    animated: config.animated !== undefined ? config.animated : true,
    persistData: config.persistData || false,
    validationRules: config.validationRules || {},
    
    // Internal state
    wizardId: 'wizard-' + Math.random().toString(36).substr(2, 9),
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Initialize form data
        this.initializeFormData();
        
        // Load saved progress if enabled
        if (this.saveProgress) {
            this.loadSavedProgress();
        }
        
        // Setup auto-save if persist data is enabled
        if (this.persistData) {
            this.setupAutoSave();
        }
        
        // Validate initial step
        this.validateCurrentStep();
        
        console.log('DSUI: Form Wizard initialized', {
            steps: this.steps.length,
            currentStep: this.currentStep,
            showProgress: this.showProgress
        });
    },
    
    // Computed properties
    get lastStepIndex() {
        return this.steps.length - 1;
    },
    
    get progressPercentage() {
        const totalSteps = this.showStepSummary ? this.steps.length + 1 : this.steps.length;
        return Math.round(((this.currentStep + 1) / totalSteps) * 100);
    },
    
    get isCurrentStepValid() {
        return this.steps[this.currentStep]?.valid !== false;
    },
    
    get allStepsValid() {
        return this.steps.every(step => step.valid !== false);
    },
    
    get currentStepData() {
        return this.steps[this.currentStep] || {};
    },
    
    // Form data initialization
    initializeFormData() {
        // Initialize form data with default values
        this.steps.forEach(step => {
            if (step.fields) {
                step.fields.forEach(field => {
                    if (field.name && this.formData[field.name] === undefined) {
                        this.formData[field.name] = field.default || (field.type === 'checkbox' ? false : '');
                    }
                });
            }
        });
        
        // Apply any pre-filled data from config
        if (config.data) {
            Object.assign(this.formData, config.data);
        }
    },
    
    // Step navigation
    goToStep(stepIndex) {
        if (stepIndex < 0 || stepIndex >= this.steps.length) return;
        
        const step = this.steps[stepIndex];
        if (step.disabled) return;
        
        // Validate current step before moving if validation is required
        if (this.validateOnNext && stepIndex > this.currentStep) {
            if (!this.validateCurrentStep()) {
                return;
            }
        }
        
        this.currentStep = stepIndex;
        this.validateCurrentStep();
        this.saveProgressIfEnabled();
        
        this.$dispatch('ds-wizard-step-change', {
            currentStep: this.currentStep,
            step: this.currentStepData,
            direction: stepIndex > this.currentStep ? 'forward' : 'backward'
        });
    },
    
    nextStep() {
        if (this.currentStep >= this.lastStepIndex) return;
        
        if (this.validateOnNext && !this.validateCurrentStep()) {
            return;
        }
        
        this.markStepCompleted(this.currentStep);
        this.goToStep(this.currentStep + 1);
    },
    
    previousStep() {
        if (this.showStepSummary && this.currentStep === this.steps.length) {
            // From summary back to last step
            this.currentStep = this.lastStepIndex;
        } else if (this.currentStep > 0) {
            this.goToStep(this.currentStep - 1);
        }
    },
    
    goToSummary() {
        if (!this.showStepSummary) return;
        
        // Validate all steps before going to summary
        if (!this.validateAllSteps()) {
            return;
        }
        
        this.markStepCompleted(this.currentStep);
        this.currentStep = this.steps.length; // Summary is beyond the last step
        
        this.$dispatch('ds-wizard-summary', {
            formData: this.formData,
            steps: this.steps
        });
    },
    
    markStepCompleted(stepIndex) {
        if (this.steps[stepIndex]) {
            this.steps[stepIndex].completed = true;
        }
    },
    
    // Validation
    validateCurrentStep() {
        const step = this.steps[this.currentStep];
        if (!step || !step.fields) {
            step.valid = true;
            return true;
        }
        
        let isValid = true;
        const stepErrors = {};
        
        step.fields.forEach(field => {
            const fieldError = this.validateField(field);
            if (fieldError) {
                stepErrors[field.name] = fieldError;
                isValid = false;
            }
        });
        
        // Update field errors
        Object.assign(this.fieldErrors, stepErrors);
        
        // Update step validity
        step.valid = isValid;
        
        return isValid;
    },
    
    validateField(field) {
        const value = this.formData[field.name];
        
        // Required validation
        if (field.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
            return `${field.label} is required`;
        }
        
        // Skip other validations if field is empty and not required
        if (!value || (typeof value === 'string' && value.trim() === '')) {
            return null;
        }
        
        // Type-specific validation
        switch (field.type) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    return 'Please enter a valid email address';
                }
                break;
                
            case 'text':
            case 'textarea':
                if (field.minLength && value.length < field.minLength) {
                    return `${field.label} must be at least ${field.minLength} characters`;
                }
                if (field.maxLength && value.length > field.maxLength) {
                    return `${field.label} must not exceed ${field.maxLength} characters`;
                }
                break;
                
            case 'password':
                if (field.minLength && value.length < field.minLength) {
                    return `Password must be at least ${field.minLength} characters`;
                }
                if (field.requireNumbers && !/\d/.test(value)) {
                    return 'Password must contain at least one number';
                }
                if (field.requireSpecialChars && !/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
                    return 'Password must contain at least one special character';
                }
                break;
        }
        
        // Custom validation rules
        if (this.validationRules[field.name]) {
            const customRule = this.validationRules[field.name];
            if (typeof customRule === 'function') {
                const result = customRule(value, this.formData);
                if (result !== true) {
                    return result || `${field.label} is invalid`;
                }
            }
        }
        
        // Pattern validation
        if (field.pattern) {
            const regex = new RegExp(field.pattern);
            if (!regex.test(value)) {
                return field.patternMessage || `${field.label} format is invalid`;
            }
        }
        
        return null;
    },
    
    validateAllSteps() {
        let allValid = true;
        
        this.steps.forEach((step, index) => {
            const originalStep = this.currentStep;
            this.currentStep = index;
            
            if (!this.validateCurrentStep()) {
                allValid = false;
            }
            
            this.currentStep = originalStep;
        });
        
        return allValid;
    },
    
    clearFieldError(fieldName) {
        if (this.fieldErrors[fieldName]) {
            delete this.fieldErrors[fieldName];
        }
    },
    
    // Form submission
    async handleSubmit() {
        if (this.loading) return;
        
        // Final validation
        if (!this.validateAllSteps()) {
            this.$dispatch('ds-wizard-validation-error', {
                errors: this.fieldErrors,
                steps: this.steps
            });
            return;
        }
        
        this.loading = true;
        
        try {
            // Emit pre-submit event
            const preSubmitEvent = new CustomEvent('ds-wizard-pre-submit', {
                detail: {
                    formData: this.formData,
                    steps: this.steps,
                    canCancel: true
                },
                cancelable: true
            });
            
            this.$el.dispatchEvent(preSubmitEvent);
            if (preSubmitEvent.defaultPrevented) {
                this.loading = false;
                return;
            }
            
            // Submit form data
            if (config.onSubmit) {
                await config.onSubmit(this.formData, this.steps);
            } else if (this.htmxAction) {
                // Use HTMX for submission
                await this.submitWithHtmx();
            } else {
                // Default form submission
                this.$el.querySelector('form').submit();
                return;
            }
            
            this.submitted = true;
            this.clearSavedProgress();
            
            this.$dispatch('ds-wizard-submit-success', {
                formData: this.formData,
                steps: this.steps
            });
            
        } catch (error) {
            console.error('DSUI: Form wizard submission failed', error);
            
            this.$dispatch('ds-wizard-submit-error', {
                error: error,
                formData: this.formData,
                steps: this.steps
            });
        } finally {
            this.loading = false;
        }
    },
    
    async submitWithHtmx() {
        const response = await fetch(this.htmxAction, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                formData: this.formData,
                steps: this.steps.map(step => ({
                    id: step.id,
                    title: step.title,
                    completed: step.completed
                }))
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.json();
    },
    
    // Progress saving/loading
    saveProgressIfEnabled() {
        if (!this.saveProgress) return;
        
        const progressData = {
            currentStep: this.currentStep,
            formData: this.formData,
            steps: this.steps.map(step => ({
                id: step.id,
                completed: step.completed,
                valid: step.valid
            }))
        };
        
        localStorage.setItem(this.progressKey, JSON.stringify(progressData));
    },
    
    loadSavedProgress() {
        try {
            const saved = localStorage.getItem(this.progressKey);
            if (saved) {
                const progressData = JSON.parse(saved);
                
                this.currentStep = progressData.currentStep || 0;
                Object.assign(this.formData, progressData.formData || {});
                
                // Restore step states
                if (progressData.steps) {
                    progressData.steps.forEach((savedStep, index) => {
                        if (this.steps[index]) {
                            this.steps[index].completed = savedStep.completed;
                            this.steps[index].valid = savedStep.valid;
                        }
                    });
                }
            }
        } catch (error) {
            console.warn('DSUI: Failed to load saved progress', error);
        }
    },
    
    clearSavedProgress() {
        if (this.saveProgress) {
            localStorage.removeItem(this.progressKey);
        }
    },
    
    setupAutoSave() {
        // Save form data changes automatically
        this.$watch('formData', () => {
            this.saveProgressIfEnabled();
        }, { deep: true });
    },
    
    // Utility methods
    getFieldDisplayValue(field) {
        const value = this.formData[field.name];
        
        if (field.type === 'select' || field.type === 'radio') {
            const option = field.options?.find(opt => opt.value === value);
            return option ? option.label : value;
        }
        
        if (field.type === 'checkbox') {
            return value ? 'Yes' : 'No';
        }
        
        return value;
    },
    
    resetWizard() {
        this.currentStep = 0;
        this.formData = {};
        this.fieldErrors = {};
        this.submitted = false;
        this.initializeFormData();
        this.clearSavedProgress();
        
        // Reset step states
        this.steps.forEach(step => {
            step.completed = false;
            step.valid = true;
        });
        
        this.$dispatch('ds-wizard-reset');
    },
    
    // Public API
    goTo(stepIndex) {
        this.goToStep(stepIndex);
    },
    
    next() {
        this.nextStep();
    },
    
    previous() {
        this.previousStep();
    },
    
    validate() {
        return this.validateCurrentStep();
    },
    
    submit() {
        this.handleSubmit();
    },
    
    reset() {
        this.resetWizard();
    },
    
    setFieldValue(fieldName, value) {
        this.formData[fieldName] = value;
        this.clearFieldError(fieldName);
        this.saveProgressIfEnabled();
    },
    
    getFieldValue(fieldName) {
        return this.formData[fieldName];
    },
    
    setStepData(stepIndex, data) {
        if (this.steps[stepIndex]) {
            Object.assign(this.steps[stepIndex], data);
        }
    },
    
    getStepData(stepIndex) {
        return this.steps[stepIndex];
    }
});