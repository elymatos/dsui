{{-- Toast Container Component --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
     role="region" 
     aria-live="polite" 
     aria-label="Notifications">
    
    {{-- Toast Messages --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div class="ds-toast"
             :class="[
                 `ds-toast--${toast.type}`,
                 `ds-toast--${toast.size}`,
                 `ds-toast--${animation}`,
                 {
                     'ds-toast--dismissible': toast.dismissible,
                     'ds-toast--progress': showProgress && toast.duration > 0,
                     'ds-toast--paused': toast.paused,
                     'ds-toast--entering': toast.entering,
                     'ds-toast--leaving': toast.leaving
                 }
             ]"
             :style="stackable ? `z-index: ${1000 + toast.stackIndex}` : ''"
             x-show="toast.visible"
             x-transition:enter="ds-toast-enter"
             x-transition:enter-start="ds-toast-enter-start"
             x-transition:enter-end="ds-toast-enter-end"
             x-transition:leave="ds-toast-leave"
             x-transition:leave-start="ds-toast-leave-start"
             x-transition:leave-end="ds-toast-leave-end"
             @mouseenter="pauseOnHover && pauseToast(toast.id)"
             @mouseleave="pauseOnHover && resumeToast(toast.id)"
             @click="clickToClose && removeToast(toast.id)"
             @touchstart="swipeToClose && handleTouchStart($event, toast.id)"
             @touchmove="swipeToClose && handleTouchMove($event, toast.id)"
             @touchend="swipeToClose && handleTouchEnd($event, toast.id)"
             role="alert"
             :aria-live="toast.type === 'danger' ? 'assertive' : 'polite'"
             tabindex="0">
            
            {{-- Toast Icon --}}
            <div class="ds-toast-icon" x-show="toast.icon || getDefaultIcon(toast.type)">
                <span x-html="toast.icon || getDefaultIcon(toast.type)"></span>
            </div>
            
            {{-- Toast Content --}}
            <div class="ds-toast-content">
                {{-- Title --}}
                <div class="ds-toast-title" 
                     x-show="toast.title" 
                     x-text="toast.title"></div>
                
                {{-- Message --}}
                <div class="ds-toast-message" 
                     x-html="toast.message"></div>
                
                {{-- Actions --}}
                <div class="ds-toast-actions" x-show="toast.actions && toast.actions.length > 0">
                    <template x-for="action in toast.actions" :key="action.id">
                        <button type="button"
                                class="ds-toast-action"
                                :class="`ds-toast-action--${action.variant || 'primary'}`"
                                @click.stop="handleAction(action, toast.id)"
                                x-text="action.label"></button>
                    </template>
                </div>
            </div>
            
            {{-- Close Button --}}
            <button type="button"
                    class="ds-toast-close"
                    x-show="toast.dismissible"
                    @click.stop="removeToast(toast.id)"
                    aria-label="Close notification"
                    tabindex="0">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
            
            {{-- Progress Bar --}}
            <div class="ds-toast-progress" 
                 x-show="showProgress && toast.duration > 0 && !toast.paused">
                <div class="ds-toast-progress-bar"
                     :style="`animation-duration: ${toast.duration}ms; animation-play-state: ${toast.paused ? 'paused' : 'running'}`"></div>
            </div>
        </div>
    </template>
    
    {{-- Default slot content (for manual toast content) --}}
    <div class="ds-toast-default-content" x-show="toasts.length === 0">
        {{ $slot }}
    </div>
</div>

{{-- Toast Creation Helper (can be used globally) --}}
<script>
// Global toast helper function
window.createToast = function(message, options = {}) {
    const event = new CustomEvent('ds-toast-create', {
        detail: {
            message: message,
            ...options
        }
    });
    document.dispatchEvent(event);
};

// Convenience methods
window.toast = {
    success: (message, options = {}) => createToast(message, { ...options, type: 'success' }),
    error: (message, options = {}) => createToast(message, { ...options, type: 'danger' }),
    warning: (message, options = {}) => createToast(message, { ...options, type: 'warning' }),
    info: (message, options = {}) => createToast(message, { ...options, type: 'info' }),
    show: (message, options = {}) => createToast(message, options)
};
</script>