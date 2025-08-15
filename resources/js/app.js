import './bootstrap';

// DSUI Design System - Core JavaScript
import Alpine from 'alpinejs';
import htmx from 'htmx.org';

// Make Alpine globally available
window.Alpine = Alpine;

// Initialize HTMX
window.htmx = htmx;

// Configure HTMX defaults
htmx.config.globalViewTransitions = true;
htmx.config.useTemplateFragments = true;

// DSUI Component Registry - Must be defined BEFORE component imports
window.DS = {
    component: {},
    store: Alpine.store,
    data: Alpine.data
};

// DSUI Base Component Functionality
DS.component.base = (config = {}) => ({
    loading: false,
    disabled: config.disabled || false,
    variant: config.variant || 'primary',
    
    init() {
        // Base component initialization
        console.log('DSUI: Component initialized', this.$el);
        
        // Add base component class
        this.$el.classList.add('ds-component');
        
        // Watch for loading state changes
        this.$watch('loading', (value) => {
            if (value) {
                this.$el.classList.add('is-loading');
                this.$el.setAttribute('aria-busy', 'true');
            } else {
                this.$el.classList.remove('is-loading');
                this.$el.removeAttribute('aria-busy');
            }
        });
        
        // Watch for disabled state changes
        this.$watch('disabled', (value) => {
            if (value) {
                this.$el.setAttribute('disabled', '');
                this.$el.setAttribute('aria-disabled', 'true');
            } else {
                this.$el.removeAttribute('disabled');
                this.$el.removeAttribute('aria-disabled');
            }
        });
    },
    
    // Common component methods
    setLoading(state) {
        this.loading = state;
    },
    
    setDisabled(state) {
        this.disabled = state;
    }
});

// DSUI Button Component
DS.component.button = (config = {}) => ({
    ...DS.component.base(config),
    
    async handleClick() {
        if (this.disabled || this.loading) return;
        
        this.loading = true;
        
        try {
            // Custom click handling
            if (config.onClick) {
                await config.onClick();
            }
            
            // Emit custom event for parent components
            this.$dispatch('ds-button-click', { 
                variant: this.variant,
                element: this.$el 
            });
            
        } catch (error) {
            console.error('DSUI: Button click error', error);
            this.$dispatch('ds-button-error', { error });
        } finally {
            this.loading = false;
        }
    }
});

// DSUI Form Component
DS.component.form = (config = {}) => ({
    ...DS.component.base(config),
    
    submitting: false,
    errors: {},
    values: config.values || {},
    
    async handleSubmit() {
        if (this.submitting) return;
        
        this.submitting = true;
        this.errors = {};
        
        try {
            // Custom submit handling
            if (config.onSubmit) {
                await config.onSubmit(this.values);
            }
            
            this.$dispatch('ds-form-submit', { 
                values: this.values,
                element: this.$el 
            });
            
        } catch (error) {
            console.error('DSUI: Form submit error', error);
            this.$dispatch('ds-form-error', { error });
        } finally {
            this.submitting = false;
        }
    },
    
    setError(field, message) {
        this.errors[field] = message;
    },
    
    clearErrors() {
        this.errors = {};
    }
});

// DSUI Modal Component
DS.component.modal = (config = {}) => ({
    ...DS.component.base(config),
    
    open: config.open || false,
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Watch for open state changes
        this.$watch('open', (value) => {
            if (value) {
                this.openModal();
            } else {
                this.closeModal();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.open) {
                this.close();
            }
        });
    },
    
    openModal() {
        document.body.classList.add('is-clipped');
        this.$el.classList.add('is-active');
        
        // Focus management
        const firstFocusable = this.$el.querySelector('[autofocus], input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }
        
        this.$dispatch('ds-modal-open');
    },
    
    closeModal() {
        document.body.classList.remove('is-clipped');
        this.$el.classList.remove('is-active');
        this.$dispatch('ds-modal-close');
    },
    
    open() {
        this.open = true;
    },
    
    close() {
        this.open = false;
    }
});

// DSUI Notification Store
Alpine.store('notifications', {
    items: [],
    
    add(notification) {
        const id = Date.now() + Math.random();
        const item = {
            id,
            type: notification.type || 'info',
            title: notification.title || '',
            message: notification.message || '',
            duration: notification.duration || 5000,
            ...notification
        };
        
        this.items.push(item);
        
        // Auto remove after duration
        if (item.duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, item.duration);
        }
        
        return id;
    },
    
    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
    },
    
    clear() {
        this.items = [];
    }
});

// HTMX Integration with Alpine
document.addEventListener('htmx:beforeRequest', (event) => {
    console.log('DSUI: HTMX request starting', event.detail);
});

document.addEventListener('htmx:afterRequest', (event) => {
    console.log('DSUI: HTMX request completed', event.detail);
    
    // Re-initialize Alpine components in new content
    if (event.detail.xhr.status === 200) {
        Alpine.initTree(event.target);
    }
});

document.addEventListener('htmx:responseError', (event) => {
    console.error('DSUI: HTMX response error', event.detail);
    
    // Add error notification
    Alpine.store('notifications').add({
        type: 'danger',
        title: 'Request Failed',
        message: 'An error occurred while processing your request.',
        duration: 5000
    });
});

// Initialize components after DS is available
async function initializeComponents() {
    // Dynamic imports to ensure DS is available
    await import('./components/button.js');
    await import('./components/input.js');
    await import('./components/textarea.js');
    await import('./components/select.js');
    await import('./components/checkbox.js');
    await import('./components/radio.js');
    await import('./components/heading.js');
    await import('./components/text.js');
    await import('./components/link.js');
    await import('./components/container.js');
    await import('./components/alert.js');
    await import('./components/modal.js');
    await import('./components/tabs.js');
    await import('./components/toast.js');
    await import('./components/data-table.js');
    await import('./components/dropdown.js');
    await import('./components/form-wizard.js');
    await import('./components/tooltip.js');
    await import('./components/popover.js');
    
    console.log('DSUI: All component modules loaded');
    console.log('DSUI: Available components:', Object.keys(DS.component));
    
    // Start Alpine after all components are loaded
    Alpine.start();
    console.log('DSUI: Design System initialized! 🎨');
}

// Initialize everything
initializeComponents();
