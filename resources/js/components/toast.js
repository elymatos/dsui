// DSUI Toast Component
DS.component.toast = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core state
    toasts: [],
    position: config.position || 'top-right',
    maxToasts: config.maxToasts || 5,
    defaultDuration: config.defaultDuration || 5000,
    pauseOnHover: config.pauseOnHover !== undefined ? config.pauseOnHover : true,
    clickToClose: config.clickToClose !== undefined ? config.clickToClose : true,
    swipeToClose: config.swipeToClose !== undefined ? config.swipeToClose : true,
    showProgress: config.showProgress !== undefined ? config.showProgress : true,
    stackable: config.stackable !== undefined ? config.stackable : true,
    animation: config.animation || 'slide',
    
    // Internal state
    nextId: 1,
    timers: new Map(),
    touchStartX: 0,
    touchStartY: 0,
    touchCurrentX: 0,
    swipeThreshold: 100,
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Listen for global toast creation events
        document.addEventListener('ds-toast-create', (event) => {
            this.addToast(event.detail);
        });
        
        // Position the container
        this.positionContainer();
        
        // Setup Alpine store integration
        if (window.Alpine && Alpine.store('notifications')) {
            this.setupStoreIntegration();
        }
        
        console.log('DSUI: Toast component initialized', {
            position: this.position,
            maxToasts: this.maxToasts
        });
    },
    
    // Toast Management
    addToast(options = {}) {
        const toast = this.createToast(options);
        
        // Remove oldest toast if at max capacity
        while (this.toasts.length >= this.maxToasts) {
            this.removeOldestToast();
        }
        
        // Add new toast
        this.toasts.push(toast);
        
        // Update stack indices
        this.updateStackIndices();
        
        // Start auto-hide timer if duration is set
        if (toast.duration > 0) {
            this.startTimer(toast.id, toast.duration);
        }
        
        // Emit event
        this.$dispatch('ds-toast-added', { toast });
        
        return toast.id;
    },
    
    createToast(options) {
        const id = this.nextId++;
        
        return {
            id: id,
            type: options.type || 'info',
            size: options.size || 'normal',
            title: options.title || '',
            message: options.message || '',
            icon: options.icon || null,
            duration: options.duration !== undefined ? options.duration : this.defaultDuration,
            dismissible: options.dismissible !== undefined ? options.dismissible : true,
            actions: options.actions || [],
            data: options.data || {},
            
            // State
            visible: true,
            paused: false,
            entering: true,
            leaving: false,
            stackIndex: 0,
            
            // Timestamps
            createdAt: Date.now(),
            pausedAt: null,
            pausedDuration: 0
        };
    },
    
    removeToast(id) {
        const toastIndex = this.toasts.findIndex(t => t.id === id);
        if (toastIndex === -1) return;
        
        const toast = this.toasts[toastIndex];
        
        // Set leaving state
        toast.leaving = true;
        
        // Clear timer
        this.clearTimer(id);
        
        // Remove after animation
        setTimeout(() => {
            const currentIndex = this.toasts.findIndex(t => t.id === id);
            if (currentIndex !== -1) {
                this.toasts.splice(currentIndex, 1);
                this.updateStackIndices();
            }
        }, 300); // Match CSS transition duration
        
        // Emit event
        this.$dispatch('ds-toast-removed', { toast });
        
        return true;
    },
    
    removeOldestToast() {
        if (this.toasts.length === 0) return;
        
        // Find oldest non-paused toast
        const oldestToast = this.toasts
            .filter(t => !t.paused)
            .sort((a, b) => a.createdAt - b.createdAt)[0];
        
        if (oldestToast) {
            this.removeToast(oldestToast.id);
        }
    },
    
    clearAll() {
        // Clear all timers
        this.timers.forEach((timer, id) => {
            this.clearTimer(id);
        });
        
        // Remove all toasts
        this.toasts.forEach(toast => {
            toast.leaving = true;
        });
        
        // Clear array after animation
        setTimeout(() => {
            this.toasts = [];
        }, 300);
        
        this.$dispatch('ds-toast-cleared');
    },
    
    // Timer Management
    startTimer(id, duration) {
        this.clearTimer(id);
        
        const timer = setTimeout(() => {
            this.removeToast(id);
        }, duration);
        
        this.timers.set(id, timer);
    },
    
    clearTimer(id) {
        const timer = this.timers.get(id);
        if (timer) {
            clearTimeout(timer);
            this.timers.delete(id);
        }
    },
    
    pauseToast(id) {
        const toast = this.toasts.find(t => t.id === id);
        if (!toast || toast.paused) return;
        
        toast.paused = true;
        toast.pausedAt = Date.now();
        
        // Clear existing timer
        this.clearTimer(id);
        
        this.$dispatch('ds-toast-paused', { toast });
    },
    
    resumeToast(id) {
        const toast = this.toasts.find(t => t.id === id);
        if (!toast || !toast.paused) return;
        
        toast.paused = false;
        
        // Calculate remaining duration
        if (toast.duration > 0) {
            const pausedDuration = Date.now() - toast.pausedAt;
            toast.pausedDuration += pausedDuration;
            
            const elapsedTime = Date.now() - toast.createdAt - toast.pausedDuration;
            const remainingTime = Math.max(0, toast.duration - elapsedTime);
            
            if (remainingTime > 0) {
                this.startTimer(id, remainingTime);
            } else {
                this.removeToast(id);
            }
        }
        
        this.$dispatch('ds-toast-resumed', { toast });
    },
    
    // Touch/Swipe Handling
    handleTouchStart(event, id) {
        const touch = event.touches[0];
        this.touchStartX = touch.clientX;
        this.touchStartY = touch.clientY;
        this.touchCurrentX = touch.clientX;
        
        // Pause toast during swipe
        this.pauseToast(id);
    },
    
    handleTouchMove(event, id) {
        if (!event.touches[0]) return;
        
        this.touchCurrentX = event.touches[0].clientX;
        const deltaX = this.touchCurrentX - this.touchStartX;
        const deltaY = Math.abs(event.touches[0].clientY - this.touchStartY);
        
        // Only handle horizontal swipes
        if (deltaY < 50) {
            event.preventDefault();
            
            // Apply transform to toast
            const toast = event.currentTarget;
            const progress = Math.min(Math.abs(deltaX) / this.swipeThreshold, 1);
            
            toast.style.transform = `translateX(${deltaX}px)`;
            toast.style.opacity = 1 - (progress * 0.7);
        }
    },
    
    handleTouchEnd(event, id) {
        const deltaX = this.touchCurrentX - this.touchStartX;
        const deltaY = Math.abs(event.changedTouches[0].clientY - this.touchStartY);
        
        // Reset toast position
        const toast = event.currentTarget;
        toast.style.transform = '';
        toast.style.opacity = '';
        
        // Check if swipe threshold was met
        if (deltaY < 50 && Math.abs(deltaX) > this.swipeThreshold) {
            this.removeToast(id);
        } else {
            // Resume toast if not removed
            this.resumeToast(id);
        }
    },
    
    // Actions
    handleAction(action, toastId) {
        const toast = this.toasts.find(t => t.id === toastId);
        if (!toast) return;
        
        // Execute action callback
        if (action.callback) {
            try {
                action.callback(toast, action);
            } catch (error) {
                console.error('DSUI: Toast action error', error);
            }
        }
        
        // Remove toast if action specifies
        if (action.closeOnClick !== false) {
            this.removeToast(toastId);
        }
        
        this.$dispatch('ds-toast-action', { 
            action, 
            toast, 
            toastId 
        });
    },
    
    // Utility Methods
    updateStackIndices() {
        this.toasts.forEach((toast, index) => {
            toast.stackIndex = index;
        });
    },
    
    positionContainer() {
        const positions = {
            'top-left': { top: '1rem', left: '1rem' },
            'top-center': { top: '1rem', left: '50%', transform: 'translateX(-50%)' },
            'top-right': { top: '1rem', right: '1rem' },
            'middle-left': { top: '50%', left: '1rem', transform: 'translateY(-50%)' },
            'middle-center': { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' },
            'middle-right': { top: '50%', right: '1rem', transform: 'translateY(-50%)' },
            'bottom-left': { bottom: '1rem', left: '1rem' },
            'bottom-center': { bottom: '1rem', left: '50%', transform: 'translateX(-50%)' },
            'bottom-right': { bottom: '1rem', right: '1rem' }
        };
        
        const position = positions[this.position];
        if (position) {
            Object.assign(this.$el.style, position);
        }
    },
    
    getDefaultIcon(type) {
        const icons = {
            success: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>',
            danger: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>',
            warning: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>',
            info: '<svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>'
        };
        
        return icons[type] || icons.info;
    },
    
    getToast(id) {
        return this.toasts.find(t => t.id === id);
    },
    
    // Alpine Store Integration
    setupStoreIntegration() {
        // Listen to Alpine notification store
        this.$watch(() => Alpine.store('notifications').items, (items) => {
            // Convert store notifications to toasts
            items.forEach(item => {
                if (!this.toasts.find(t => t.data.storeId === item.id)) {
                    this.addToast({
                        type: item.type,
                        title: item.title,
                        message: item.message,
                        duration: item.duration,
                        data: { storeId: item.id }
                    });
                }
            });
        });
    },
    
    // Public API
    show(message, options = {}) {
        return this.addToast({ message, ...options });
    },
    
    success(message, options = {}) {
        return this.addToast({ message, type: 'success', ...options });
    },
    
    error(message, options = {}) {
        return this.addToast({ message, type: 'danger', ...options });
    },
    
    warning(message, options = {}) {
        return this.addToast({ message, type: 'warning', ...options });
    },
    
    info(message, options = {}) {
        return this.addToast({ message, type: 'info', ...options });
    },
    
    remove(id) {
        return this.removeToast(id);
    },
    
    clear() {
        this.clearAll();
    },
    
    pause(id) {
        this.pauseToast(id);
    },
    
    resume(id) {
        this.resumeToast(id);
    }
});