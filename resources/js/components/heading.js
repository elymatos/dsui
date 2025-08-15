/**
 * DSUI Heading Component
 * Alpine.js component for typography headings with optional interactive features
 */

DS.component.heading = (config = {}) => ({
    // Component state
    level: config.level || '1',
    weight: config.weight || 'normal',
    color: config.color || null,
    subtitle: config.subtitle || false,
    align: config.align || 'left',
    
    // Optional features
    copyable: config.copyable || false,
    linkable: config.linkable || false,
    animateOnView: config.animateOnView || false,
    
    // Internal state
    copied: false,
    visible: false,
    
    /**
     * Initialize the component
     */
    init() {
        // Setup copy functionality
        if (this.copyable) {
            this.setupCopyFunctionality();
        }
        
        // Setup link anchor functionality
        if (this.linkable) {
            this.setupLinkFunctionality();
        }
        
        // Setup animation on viewport entry
        if (this.animateOnView) {
            this.setupViewportAnimation();
        }
        
        // Add heading to document outline if needed
        this.registerInOutline();
    },
    
    /**
     * Setup copy to clipboard functionality
     */
    setupCopyFunctionality() {
        // Add copy button or make heading clickable for copying
        this.$el.style.cursor = 'pointer';
        this.$el.title = 'Click to copy text';
        
        this.$el.addEventListener('click', () => {
            this.copyText();
        });
    },
    
    /**
     * Copy heading text to clipboard
     */
    async copyText() {
        try {
            const text = this.$el.textContent || this.$el.innerText;
            await navigator.clipboard.writeText(text);
            
            this.copied = true;
            
            // Show feedback
            this.$dispatch('heading-copied', { text });
            
            // Reset feedback after delay
            setTimeout(() => {
                this.copied = false;
            }, 2000);
            
        } catch (error) {
            console.error('Failed to copy text:', error);
            this.$dispatch('heading-copy-error', { error });
        }
    },
    
    /**
     * Setup link anchor functionality
     */
    setupLinkFunctionality() {
        // Generate ID if not present
        if (!this.$el.id) {
            this.$el.id = this.generateHeadingId();
        }
        
        // Add link icon on hover
        const linkIcon = document.createElement('a');
        linkIcon.href = `#${this.$el.id}`;
        linkIcon.className = 'ds-heading-link';
        linkIcon.innerHTML = '#';
        linkIcon.setAttribute('aria-label', 'Link to this heading');
        
        this.$el.style.position = 'relative';
        this.$el.appendChild(linkIcon);
    },
    
    /**
     * Generate ID from heading text
     */
    generateHeadingId() {
        const text = this.$el.textContent || this.$el.innerText;
        return text
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .trim();
    },
    
    /**
     * Setup viewport animation
     */
    setupViewportAnimation() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.visible = true;
                    this.$el.classList.add('ds-heading--animated');
                    observer.unobserve(this.$el);
                }
            });
        }, {
            threshold: 0.1
        });
        
        observer.observe(this.$el);
    },
    
    /**
     * Register heading in document outline
     */
    registerInOutline() {
        // Emit event for table of contents generation
        this.$dispatch('heading-registered', {
            level: this.level,
            text: this.$el.textContent || this.$el.innerText,
            id: this.$el.id,
            element: this.$el
        });
    },
    
    /**
     * Update heading text
     */
    updateText(newText) {
        this.$el.textContent = newText;
        this.registerInOutline();
    },
    
    /**
     * Change heading level dynamically
     */
    changeLevel(newLevel) {
        this.level = newLevel;
        
        // Update classes
        this.$el.className = this.$el.className.replace(/is-\d+/g, '');
        if (newLevel !== '1') {
            this.$el.classList.add(`is-${newLevel}`);
        }
        
        // Update accessibility
        if (this.$el.getAttribute('role') === 'heading') {
            this.$el.setAttribute('aria-level', newLevel);
        }
        
        this.registerInOutline();
    },
    
    /**
     * Toggle subtitle mode
     */
    toggleSubtitle() {
        this.subtitle = !this.subtitle;
        
        this.$el.classList.toggle('title');
        this.$el.classList.toggle('subtitle');
        
        this.registerInOutline();
    },
    
    /**
     * Change text alignment
     */
    setAlignment(alignment) {
        // Remove existing alignment classes
        this.$el.className = this.$el.className.replace(/has-text-(left|centered|right|justified)/g, '');
        
        // Add new alignment class
        if (alignment !== 'left') {
            this.$el.classList.add(`has-text-${alignment}`);
        }
        
        this.align = alignment;
    },
    
    /**
     * Change text color
     */
    setColor(color) {
        // Remove existing color classes
        this.$el.className = this.$el.className.replace(/has-text-\w+/g, '');
        
        // Add new color class
        if (color) {
            this.$el.classList.add(`has-text-${color}`);
        }
        
        this.color = color;
    },
    
    /**
     * Get heading outline data
     */
    getOutlineData() {
        return {
            level: this.level,
            text: this.$el.textContent || this.$el.innerText,
            id: this.$el.id,
            subtitle: this.subtitle
        };
    }
});