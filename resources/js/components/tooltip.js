// DSUI Tooltip Component
DS.component.tooltip = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core state
    visible: false,
    content: config.content || '',
    position: config.position || 'top',
    trigger: config.trigger || 'hover',
    
    // Configuration
    delay: config.delay || 0,
    hideDelay: config.hideDelay || 0,
    arrow: config.arrow !== undefined ? config.arrow : true,
    theme: config.theme || 'dark',
    maxWidth: config.maxWidth || 200,
    interactive: config.interactive || false,
    followCursor: config.followCursor || false,
    boundary: config.boundary || 'viewport',
    offset: config.offset || 8,
    html: config.html || false,
    animation: config.animation !== undefined ? config.animation : true,
    animationType: config.animationType || 'fade',
    
    // Internal state
    tooltipId: 'tooltip-' + Math.random().toString(36).substr(2, 9),
    showTimeout: null,
    hideTimeout: null,
    tooltipElement: null,
    triggerElement: null,
    currentPosition: { x: 0, y: 0 },
    calculatedPosition: {},
    
    init() {
        DS.component.base(config).init.call(this);
        
        this.triggerElement = this.$el;
        this.tooltipElement = this.$el.querySelector('.ds-tooltip');
        
        // Setup global click handler for click trigger
        if (this.trigger === 'click') {
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target) && this.visible) {
                    this.hideTooltip(e);
                }
            });
        }
        
        // Setup escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.visible) {
                this.hideTooltip(e);
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
            if (this.visible && !this.followCursor) {
                this.updatePosition();
            }
        }, { passive: true });
        
        console.log('DSUI: Tooltip initialized', {
            trigger: this.trigger,
            position: this.position,
            theme: this.theme
        });
    },
    
    // Show/Hide Methods
    showTooltip(event) {
        if (this.disabled || !this.content) return;
        
        this.clearTimeouts();
        
        if (this.delay > 0) {
            this.showTimeout = setTimeout(() => {
                this.show(event);
            }, this.delay);
        } else {
            this.show(event);
        }
    },
    
    hideTooltip(event) {
        if (!this.visible) return;
        
        this.clearTimeouts();
        
        if (this.hideDelay > 0) {
            this.hideTimeout = setTimeout(() => {
                this.hide(event);
            }, this.hideDelay);
        } else {
            this.hide(event);
        }
    },
    
    toggleTooltip(event) {
        if (this.visible) {
            this.hideTooltip(event);
        } else {
            this.showTooltip(event);
        }
    },
    
    show(event) {
        this.visible = true;
        
        this.$nextTick(() => {
            this.updatePosition(event);
            
            // Focus management for accessibility
            if (this.trigger === 'click' || this.trigger === 'focus') {
                this.tooltipElement?.setAttribute('tabindex', '-1');
            }
        });
        
        this.$dispatch('ds-tooltip-show', {
            content: this.content,
            position: this.position,
            trigger: this.trigger
        });
    },
    
    hide(event) {
        this.visible = false;
        
        this.$dispatch('ds-tooltip-hide', {
            content: this.content,
            position: this.position,
            trigger: this.trigger
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
    
    // Position Calculation
    updatePosition(event = null) {
        if (!this.tooltipElement || !this.triggerElement) return;
        
        if (this.followCursor && event) {
            this.updateCursorPosition(event);
            return;
        }
        
        const triggerRect = this.triggerElement.getBoundingClientRect();
        const tooltipRect = this.tooltipElement.getBoundingClientRect();
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight,
            scrollX: window.scrollX,
            scrollY: window.scrollY
        };
        
        let position = this.calculatePosition(triggerRect, tooltipRect, viewport);
        
        // Check boundaries and adjust if necessary
        position = this.adjustForBoundaries(position, tooltipRect, viewport);
        
        this.calculatedPosition = position;
    },
    
    updateCursorPosition(event) {
        this.currentPosition = {
            x: event.clientX,
            y: event.clientY
        };
        
        const tooltipRect = this.tooltipElement?.getBoundingClientRect();
        if (!tooltipRect) return;
        
        const position = this.calculateCursorPosition(this.currentPosition, tooltipRect);
        this.calculatedPosition = position;
    },
    
    calculatePosition(triggerRect, tooltipRect, viewport) {
        const offset = this.offset;
        let x = 0;
        let y = 0;
        
        // Base position calculation
        switch (this.getMainPosition()) {
            case 'top':
                x = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
                y = triggerRect.top - tooltipRect.height - offset;
                break;
                
            case 'bottom':
                x = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
                y = triggerRect.bottom + offset;
                break;
                
            case 'left':
                x = triggerRect.left - tooltipRect.width - offset;
                y = triggerRect.top + (triggerRect.height / 2) - (tooltipRect.height / 2);
                break;
                
            case 'right':
                x = triggerRect.right + offset;
                y = triggerRect.top + (triggerRect.height / 2) - (tooltipRect.height / 2);
                break;
        }
        
        // Adjust for position modifiers (start, end)
        const modifier = this.getPositionModifier();
        if (modifier) {
            if (this.getMainPosition() === 'top' || this.getMainPosition() === 'bottom') {
                if (modifier === 'start') {
                    x = triggerRect.left;
                } else if (modifier === 'end') {
                    x = triggerRect.right - tooltipRect.width;
                }
            } else {
                if (modifier === 'start') {
                    y = triggerRect.top;
                } else if (modifier === 'end') {
                    y = triggerRect.bottom - tooltipRect.height;
                }
            }
        }
        
        return { x: x + viewport.scrollX, y: y + viewport.scrollY };
    },
    
    calculateCursorPosition(cursor, tooltipRect) {
        const offset = this.offset;
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight,
            scrollX: window.scrollX,
            scrollY: window.scrollY
        };
        
        let x = cursor.x + offset;
        let y = cursor.y - tooltipRect.height - offset;
        
        // Adjust if tooltip would go outside viewport
        if (x + tooltipRect.width > viewport.width) {
            x = cursor.x - tooltipRect.width - offset;
        }
        
        if (y < 0) {
            y = cursor.y + offset;
        }
        
        return { x: x + viewport.scrollX, y: y + viewport.scrollY };
    },
    
    adjustForBoundaries(position, tooltipRect, viewport) {
        const padding = 8; // Minimum distance from viewport edge
        
        // Horizontal boundaries
        if (position.x < viewport.scrollX + padding) {
            position.x = viewport.scrollX + padding;
        } else if (position.x + tooltipRect.width > viewport.scrollX + viewport.width - padding) {
            position.x = viewport.scrollX + viewport.width - tooltipRect.width - padding;
        }
        
        // Vertical boundaries
        if (position.y < viewport.scrollY + padding) {
            position.y = viewport.scrollY + padding;
        } else if (position.y + tooltipRect.height > viewport.scrollY + viewport.height - padding) {
            position.y = viewport.scrollY + viewport.height - tooltipRect.height - padding;
        }
        
        return position;
    },
    
    // Style Generation
    getTooltipStyles() {
        const styles = {
            maxWidth: `${this.maxWidth}px`,
            left: `${this.calculatedPosition.x}px`,
            top: `${this.calculatedPosition.y}px`,
        };
        
        return Object.entries(styles)
            .map(([key, value]) => `${key}: ${value}`)
            .join('; ');
    },
    
    getArrowStyles() {
        // Arrow positioning would be calculated based on tooltip position
        // This is a simplified version - in production, you'd want more sophisticated arrow positioning
        return '';
    },
    
    // Transition Classes
    getEnterTransition() {
        if (!this.animation) return '';
        
        switch (this.animationType) {
            case 'fade':
                return 'transition duration-200 ease-out';
            case 'scale':
                return 'transition duration-200 ease-out';
            case 'shift-away':
                return 'transition duration-200 ease-out';
            case 'perspective':
                return 'transition duration-200 ease-out';
            default:
                return 'transition duration-200 ease-out';
        }
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
        this.showTooltip();
    },
    
    hide() {
        this.hideTooltip();
    },
    
    toggle() {
        this.toggleTooltip();
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