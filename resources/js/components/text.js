/**
 * DSUI Text Component
 * Alpine.js component for enhanced text functionality with optional interactive features
 */

DS.component.text = (config = {}) => ({
    // Component state
    element: config.element || 'p',
    textSize: config.textSize || 'normal',
    weight: config.weight || 'normal',
    color: config.color || null,
    align: config.align || 'left',
    italic: config.italic || false,
    underline: config.underline || false,
    strikethrough: config.strikethrough || false,
    truncate: config.truncate || false,
    lines: config.lines || 1,
    
    // Optional features
    copyable: config.copyable || false,
    highlightable: config.highlightable || false,
    selectable: config.selectable !== false, // Default to true
    expandable: config.expandable || false,
    
    // Internal state
    copied: false,
    expanded: false,
    highlighted: false,
    
    /**
     * Initialize the component
     */
    init() {
        // Setup copy functionality
        if (this.copyable) {
            this.setupCopyFunctionality();
        }
        
        // Setup highlight functionality
        if (this.highlightable) {
            this.setupHighlightFunctionality();
        }
        
        // Setup expandable text
        if (this.expandable && this.truncate) {
            this.setupExpandableFunctionality();
        }
        
        // Setup selectable text
        if (!this.selectable) {
            this.$el.style.userSelect = 'none';
            this.$el.style.webkitUserSelect = 'none';
        }
        
        // Setup text observers
        this.setupTextObservers();
    },
    
    /**
     * Setup copy to clipboard functionality
     */
    setupCopyFunctionality() {
        this.$el.style.cursor = 'pointer';
        this.$el.title = 'Click to copy text';
        
        this.$el.addEventListener('click', () => {
            this.copyText();
        });
    },
    
    /**
     * Copy text content to clipboard
     */
    async copyText() {
        try {
            const text = this.$el.textContent || this.$el.innerText;
            await navigator.clipboard.writeText(text);
            
            this.copied = true;
            
            // Show feedback
            this.$dispatch('text-copied', { text });
            
            // Reset feedback after delay
            setTimeout(() => {
                this.copied = false;
            }, 2000);
            
        } catch (error) {
            console.error('Failed to copy text:', error);
            this.$dispatch('text-copy-error', { error });
        }
    },
    
    /**
     * Setup highlight functionality
     */
    setupHighlightFunctionality() {
        this.$el.addEventListener('dblclick', () => {
            this.toggleHighlight();
        });
    },
    
    /**
     * Toggle text highlight
     */
    toggleHighlight() {
        this.highlighted = !this.highlighted;
        
        if (this.highlighted) {
            this.$el.classList.add('ds-text--highlighted');
        } else {
            this.$el.classList.remove('ds-text--highlighted');
        }
        
        this.$dispatch('text-highlight-change', { 
            highlighted: this.highlighted,
            text: this.$el.textContent || this.$el.innerText
        });
    },
    
    /**
     * Setup expandable functionality for truncated text
     */
    setupExpandableFunctionality() {
        // Add expand button
        const expandButton = document.createElement('button');
        expandButton.textContent = '...more';
        expandButton.className = 'ds-text-expand-btn';
        expandButton.addEventListener('click', () => {
            this.toggleExpanded();
        });
        
        this.$el.parentNode.insertBefore(expandButton, this.$el.nextSibling);
    },
    
    /**
     * Toggle expanded state
     */
    toggleExpanded() {
        this.expanded = !this.expanded;
        
        if (this.expanded) {
            this.$el.classList.remove('ds-text--truncate', 'ds-text--truncate-lines');
            this.$el.nextElementSibling.textContent = 'less';
        } else {
            if (this.lines > 1) {
                this.$el.classList.add('ds-text--truncate-lines');
            } else {
                this.$el.classList.add('ds-text--truncate');
            }
            this.$el.nextElementSibling.textContent = '...more';
        }
        
        this.$dispatch('text-expand-change', { 
            expanded: this.expanded,
            text: this.$el.textContent || this.$el.innerText
        });
    },
    
    /**
     * Setup text change observers
     */
    setupTextObservers() {
        // Watch for text content changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    this.onTextChange();
                }
            });
        });
        
        observer.observe(this.$el, {
            childList: true,
            subtree: true,
            characterData: true
        });
    },
    
    /**
     * Handle text content changes
     */
    onTextChange() {
        this.$dispatch('text-change', {
            text: this.$el.textContent || this.$el.innerText,
            element: this.$el
        });
    },
    
    /**
     * Update text content
     */
    updateText(newText) {
        this.$el.textContent = newText;
        this.onTextChange();
    },
    
    /**
     * Change text size
     */
    setTextSize(size) {
        // Remove existing size classes
        this.$el.className = this.$el.className.replace(/is-size-\w+/g, '');
        
        // Add new size class
        if (size !== 'normal') {
            this.$el.classList.add(`is-size-${size}`);
        }
        
        this.textSize = size;
    },
    
    /**
     * Change text weight
     */
    setWeight(weight) {
        // Remove existing weight classes
        this.$el.className = this.$el.className.replace(/has-text-weight-\w+/g, '');
        
        // Add new weight class
        if (weight !== 'normal') {
            this.$el.classList.add(`has-text-weight-${weight}`);
        }
        
        this.weight = weight;
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
     * Toggle italic style
     */
    toggleItalic() {
        this.italic = !this.italic;
        this.$el.classList.toggle('is-italic', this.italic);
    },
    
    /**
     * Toggle underline style
     */
    toggleUnderline() {
        this.underline = !this.underline;
        this.$el.classList.toggle('is-underlined', this.underline);
    },
    
    /**
     * Toggle strikethrough style
     */
    toggleStrikethrough() {
        this.strikethrough = !this.strikethrough;
        this.$el.classList.toggle('has-text-strikethrough', this.strikethrough);
    },
    
    /**
     * Get text content
     */
    getText() {
        return this.$el.textContent || this.$el.innerText;
    },
    
    /**
     * Get text length
     */
    getTextLength() {
        return this.getText().length;
    },
    
    /**
     * Get word count
     */
    getWordCount() {
        return this.getText().trim().split(/\s+/).length;
    },
    
    /**
     * Check if text is truncated
     */
    isTruncated() {
        return this.$el.scrollHeight > this.$el.clientHeight || 
               this.$el.scrollWidth > this.$el.clientWidth;
    }
});