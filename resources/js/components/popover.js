// DSUI Popover Component
DS.component.popover = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core state
    visible: false,
    title: config.title || '',
    content: config.content || '',
    position: config.position || 'top',
    trigger: config.trigger || 'click',
    
    // Configuration
    delay: config.delay || 0,
    hideDelay: config.hideDelay || 0,
    arrow: config.arrow !== undefined ? config.arrow : true,
    theme: config.theme || 'light',
    maxWidth: config.maxWidth || 300,
    interactive: config.interactive !== undefined ? config.interactive : true,
    modal: config.modal || false,
    boundary: config.boundary || 'viewport',
    offset: config.offset || 8,
    html: config.html || false,
    animation: config.animation !== undefined ? config.animation : true,
    animationType: config.animationType || 'fade',
    closable: config.closable !== undefined ? config.closable : true,
    backdrop: config.backdrop || false,
    
    // Internal state
    popoverId: 'popover-' + Math.random().toString(36).substr(2, 9),
    showTimeout: null,
    hideTimeout: null,
    popoverElement: null,
    triggerElement: null,
    calculatedPosition: {},
    focusTrap: null,
    previousFocus: null,
    
    init() {
        DS.component.base(config).init.call(this);
        
        this.triggerElement = this.$el;
        this.popoverElement = this.$el.querySelector('.ds-popover');
        
        // Setup global click handler for click trigger
        if (this.trigger === 'click') {
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target) && this.visible && !this.modal) {
                    this.hidePopover(e);
                }
            });
        }
        
        // Setup escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.visible) {
                this.hidePopover(e);
            }
        });
        
        // Setup resize handler for position updates
        window.addEventListener('resize', () => {
            if (this.visible) {
                this.updatePosition();
            }
        });
        
        // Setup scroll handler for position updates
        window.addEventListener('scroll', () => {
            if (this.visible && !this.modal) {
                this.updatePosition();
            }
        }, { passive: true });
        
        console.log('DSUI: Popover initialized', {
            trigger: this.trigger,
            position: this.position,
            theme: this.theme,
            modal: this.modal
        });
    },
    
    // Show/Hide Methods
    showPopover(event) {
        if (this.disabled || (!this.content && !this.title)) return;
        
        this.clearTimeouts();
        
        if (this.delay > 0) {
            this.showTimeout = setTimeout(() => {
                this.show(event);
            }, this.delay);
        } else {
            this.show(event);
        }
    },
    
    hidePopover(event) {
        if (!this.visible) return;
        
        this.clearTimeouts();
        
        if (this.hideDelay > 0 && !this.modal) {
            this.hideTimeout = setTimeout(() => {
                this.hide(event);
            }, this.hideDelay);
        } else {
            this.hide(event);
        }
    },
    
    togglePopover(event) {
        if (this.visible) {
            this.hidePopover(event);
        } else {
            this.showPopover(event);
        }
    },
    
    show(event) {
        // Store previous focus for modal popovers
        if (this.modal) {
            this.previousFocus = document.activeElement;
        }
        
        this.visible = true;
        
        this.$nextTick(() => {
            this.updatePosition(event);
            
            // Setup focus management
            if (this.modal) {
                this.setupFocusTrap();
                this.focusFirstElement();
            }
            
            // Lock body scroll for modal popovers
            if (this.modal) {
                document.body.classList.add('ds-popover-modal-open');
            }
        });
        
        this.$dispatch('ds-popover-show', {
            title: this.title,
            content: this.content,
            position: this.position,
            trigger: this.trigger,
            modal: this.modal
        });
    },
    
    hide(event) {
        this.visible = false;
        
        // Cleanup focus management
        if (this.modal) {
            this.destroyFocusTrap();
            document.body.classList.remove('ds-popover-modal-open');
            
            // Restore previous focus
            if (this.previousFocus && this.previousFocus.focus) {
                this.previousFocus.focus();
            }
        }
        
        this.$dispatch('ds-popover-hide', {
            title: this.title,
            content: this.content,
            position: this.position,
            trigger: this.trigger,
            modal: this.modal
        });
    },
    
    clearTimeouts() {
        if (this.showTimeout) {
            clearTimeout(this.showTimeout);
            this.showTimeout = null;
        }
        if (this.hideTimeout) {
            clearTimeout(this.hideTimeout);
            this.hideTimeout = null;
        }
    },
    
    clearHideTimeout() {
        if (this.hideTimeout) {
            clearTimeout(this.hideTimeout);
            this.hideTimeout = null;
        }
    },
    
    // Position Calculation (similar to tooltip)
    updatePosition(event = null) {
        if (!this.popoverElement || !this.triggerElement) return;
        
        const triggerRect = this.triggerElement.getBoundingClientRect();
        const popoverRect = this.popoverElement.getBoundingClientRect();
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight,
            scrollX: window.scrollX,
            scrollY: window.scrollY
        };
        
        let position = this.calculatePosition(triggerRect, popoverRect, viewport);
        
        // Check boundaries and adjust if necessary
        position = this.adjustForBoundaries(position, popoverRect, viewport);
        
        this.calculatedPosition = position;
    },
    
    calculatePosition(triggerRect, popoverRect, viewport) {
        const offset = this.offset;
        let x = 0;
        let y = 0;
        
        // Base position calculation
        switch (this.getMainPosition()) {
            case 'top':
                x = triggerRect.left + (triggerRect.width / 2) - (popoverRect.width / 2);
                y = triggerRect.top - popoverRect.height - offset;
                break;
                
            case 'bottom':
                x = triggerRect.left + (triggerRect.width / 2) - (popoverRect.width / 2);
                y = triggerRect.bottom + offset;
                break;
                
            case 'left':
                x = triggerRect.left - popoverRect.width - offset;
                y = triggerRect.top + (triggerRect.height / 2) - (popoverRect.height / 2);
                break;
                
            case 'right':
                x = triggerRect.right + offset;
                y = triggerRect.top + (triggerRect.height / 2) - (popoverRect.height / 2);
                break;
        }
        
        // Adjust for position modifiers (start, end)
        const modifier = this.getPositionModifier();
        if (modifier) {
            if (this.getMainPosition() === 'top' || this.getMainPosition() === 'bottom') {
                if (modifier === 'start') {
                    x = triggerRect.left;
                } else if (modifier === 'end') {
                    x = triggerRect.right - popoverRect.width;
                }
            } else {
                if (modifier === 'start') {
                    y = triggerRect.top;
                } else if (modifier === 'end') {
                    y = triggerRect.bottom - popoverRect.height;
                }
            }
        }
        
        return { x: x + viewport.scrollX, y: y + viewport.scrollY };
    },
    
    adjustForBoundaries(position, popoverRect, viewport) {
        const padding = 16; // Minimum distance from viewport edge
        
        // For modal popovers, center them
        if (this.modal) {
            return {
                x: viewport.scrollX + (viewport.width / 2) - (popoverRect.width / 2),
                y: viewport.scrollY + (viewport.height / 2) - (popoverRect.height / 2)
            };
        }
        
        // Horizontal boundaries
        if (position.x < viewport.scrollX + padding) {
            position.x = viewport.scrollX + padding;
        } else if (position.x + popoverRect.width > viewport.scrollX + viewport.width - padding) {
            position.x = viewport.scrollX + viewport.width - popoverRect.width - padding;
        }
        
        // Vertical boundaries
        if (position.y < viewport.scrollY + padding) {
            position.y = viewport.scrollY + padding;
        } else if (position.y + popoverRect.height > viewport.scrollY + viewport.height - padding) {
            position.y = viewport.scrollY + viewport.height - popoverRect.height - padding;
        }
        
        return position;
    },
    
    // Focus Management
    setupFocusTrap() {
        if (!this.modal || !this.popoverElement) return;
        
        this.focusTrap = {
            focusableElements: this.getFocusableElements(),
            firstFocusableElement: null,
            lastFocusableElement: null
        };
        
        const elements = this.focusTrap.focusableElements;
        if (elements.length > 0) {
            this.focusTrap.firstFocusableElement = elements[0];
            this.focusTrap.lastFocusableElement = elements[elements.length - 1];
        }
        
        // Setup keydown handler for focus trapping
        this.popoverElement.addEventListener('keydown', this.handleFocusTrap.bind(this));
    },
    
    destroyFocusTrap() {
        if (this.focusTrap && this.popoverElement) {
            this.popoverElement.removeEventListener('keydown', this.handleFocusTrap.bind(this));
            this.focusTrap = null;
        }
    },
    
    handleFocusTrap(event) {
        if (!this.focusTrap || event.key !== 'Tab') return;
        
        const { firstFocusableElement, lastFocusableElement } = this.focusTrap;
        
        if (event.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstFocusableElement) {
                event.preventDefault();
                lastFocusableElement?.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastFocusableElement) {
                event.preventDefault();
                firstFocusableElement?.focus();
            }
        }
    },
    
    getFocusableElements() {
        if (!this.popoverElement) return [];
        
        const selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        return Array.from(this.popoverElement.querySelectorAll(selector))
            .filter(el => !el.disabled && !el.hidden && el.offsetParent !== null);
    },
    
    focusFirstElement() {
        if (this.focusTrap?.firstFocusableElement) {
            this.focusTrap.firstFocusableElement.focus();
        } else if (this.popoverElement) {
            this.popoverElement.focus();
        }
    },
    
    // Style Generation
    getPopoverStyles() {
        const styles = {
            maxWidth: `${this.maxWidth}px`,
        };
        
        if (!this.modal) {
            styles.left = `${this.calculatedPosition.x}px`;
            styles.top = `${this.calculatedPosition.y}px`;
        }
        
        return Object.entries(styles)
            .map(([key, value]) => `${key}: ${value}`)
            .join('; ');
    },
    
    getArrowStyles() {
        // Arrow positioning would be calculated based on popover position
        return '';
    },
    
    // Transition Classes (similar to tooltip)
    getEnterTransition() {
        if (!this.animation) return '';
        return 'transition duration-200 ease-out';
    },
    
    getEnterStartClass() {
        if (!this.animation) return '';
        
        switch (this.animationType) {
            case 'fade':
                return 'opacity-0';
            case 'scale':
                return 'opacity-0 transform scale-95';
            case 'shift-away':
                return 'opacity-0 transform translateY(-8px)';
            case 'perspective':
                return 'opacity-0 transform perspective(1000px) rotateX(-20deg)';
            default:
                return 'opacity-0';
        }
    },
    
    getEnterEndClass() {
        if (!this.animation) return '';
        
        switch (this.animationType) {
            case 'fade':
                return 'opacity-100';
            case 'scale':
                return 'opacity-100 transform scale-100';
            case 'shift-away':
                return 'opacity-100 transform translateY(0)';
            case 'perspective':
                return 'opacity-100 transform perspective(1000px) rotateX(0)';
            default:
                return 'opacity-100';
        }
    },
    
    getLeaveTransition() {
        if (!this.animation) return '';
        return 'transition duration-150 ease-in';
    },
    
    getLeaveStartClass() {
        return this.getEnterEndClass();
    },
    
    getLeaveEndClass() {
        return this.getEnterStartClass();
    },
    
    // Utility Methods
    getMainPosition() {
        return this.position.split('-')[0];
    },
    
    getPositionModifier() {
        const parts = this.position.split('-');
        return parts.length > 1 ? parts[1] : null;
    },
    
    getArrowPosition() {
        const main = this.getMainPosition();
        const opposite = {
            'top': 'bottom',
            'bottom': 'top',
            'left': 'right',
            'right': 'left'
        };
        return opposite[main] || 'bottom';
    },
    
    // Public API
    show() {
        this.showPopover();
    },
    
    hide() {
        this.hidePopover();
    },
    
    toggle() {
        this.togglePopover();
    },
    
    updateTitle(newTitle) {
        this.title = newTitle;
        if (this.visible) {
            this.updatePosition();
        }
    },
    
    updateContent(newContent) {
        this.content = newContent;
        if (this.visible) {
            this.updatePosition();
        }
    },
    
    setPosition(newPosition) {
        this.position = newPosition;
        if (this.visible) {
            this.updatePosition();
        }
    },
    
    enable() {
        this.disabled = false;
    },
    
    disable() {
        this.disabled = true;
        if (this.visible) {
            this.hide();
        }
    }
});