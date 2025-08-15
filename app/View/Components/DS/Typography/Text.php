<?php

namespace App\View\Components\DS\Typography;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Text extends BaseComponent
{
    public string $element;
    public string $textSize;
    public string $weight;
    public ?string $color;
    public string $align;
    public bool $italic;
    public bool $underline;
    public bool $strikethrough;
    public bool $truncate;
    public int $lines;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $element = 'p',
        string $textSize = 'normal',
        string $weight = 'normal',
        ?string $color = null,
        string $align = 'left',
        bool $italic = false,
        bool $underline = false,
        bool $strikethrough = false,
        bool $truncate = false,
        int $lines = 1
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->element = $element;
        $this->textSize = $textSize;
        $this->weight = $weight;
        $this->color = $color;
        $this->align = $align;
        $this->italic = $italic;
        $this->underline = $underline;
        $this->strikethrough = $strikethrough;
        $this->truncate = $truncate;
        $this->lines = $lines;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.typography.text');
    }

    /**
     * Get the HTML tag name for the text element.
     */
    public function getTagName(): string
    {
        return $this->element;
    }

    /**
     * Get base CSS classes specific to text component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-text'];
        
        // Text size class
        if ($this->textSize !== 'normal') {
            $classes[] = "is-size-{$this->textSize}";
        }
        
        // Weight class
        if ($this->weight !== 'normal') {
            $classes[] = "has-text-weight-{$this->weight}";
        }
        
        // Color class
        if ($this->color) {
            $classes[] = "has-text-{$this->color}";
        }
        
        // Alignment class
        if ($this->align !== 'left') {
            $classes[] = "has-text-{$this->align}";
        }
        
        // Style modifiers
        if ($this->italic) {
            $classes[] = 'is-italic';
        }
        
        if ($this->underline) {
            $classes[] = 'is-underlined';
        }
        
        if ($this->strikethrough) {
            $classes[] = 'has-text-strikethrough';
        }
        
        // Truncation
        if ($this->truncate) {
            if ($this->lines > 1) {
                $classes[] = 'ds-text--truncate-lines';
            } else {
                $classes[] = 'ds-text--truncate';
            }
        }
        
        return $classes;
    }

    /**
     * Get inline styles for multi-line truncation.
     */
    public function getInlineStyles(): array
    {
        $styles = [];
        
        if ($this->truncate && $this->lines > 1) {
            $styles['--ds-text-lines'] = $this->lines;
        }
        
        return $styles;
    }

    /**
     * Get valid HTML elements for text.
     */
    public function getValidElements(): array
    {
        return ['p', 'span', 'div', 'small', 'strong', 'em', 'mark', 'code', 'kbd', 'samp', 'var'];
    }

    /**
     * Get valid text sizes.
     */
    public function getValidTextSizes(): array
    {
        return ['1', '2', '3', '4', '5', '6', '7', 'normal'];
    }

    /**
     * Get valid font weights.
     */
    public function getValidWeights(): array
    {
        return ['light', 'normal', 'medium', 'semibold', 'bold'];
    }

    /**
     * Get valid text colors.
     */
    public function getValidColors(): array
    {
        return [
            'white', 'black', 'light', 'dark', 
            'primary', 'link', 'info', 'success', 'warning', 'danger',
            'grey-lighter', 'grey-light', 'grey', 'grey-dark', 'grey-darker'
        ];
    }

    /**
     * Get valid text alignments.
     */
    public function getValidAlignments(): array
    {
        return ['left', 'centered', 'right', 'justified'];
    }

    /**
     * Get semantic element type.
     */
    public function getElementType(): string
    {
        return match($this->element) {
            'p' => 'paragraph',
            'span' => 'inline',
            'div' => 'block',
            'small' => 'small',
            'strong' => 'strong',
            'em' => 'emphasis',
            'mark' => 'highlight',
            'code' => 'code',
            'kbd' => 'keyboard',
            'samp' => 'sample',
            'var' => 'variable',
            default => 'text'
        };
    }

    /**
     * Check if element is inline.
     */
    public function isInline(): bool
    {
        return in_array($this->element, ['span', 'strong', 'em', 'mark', 'code', 'kbd', 'samp', 'var']);
    }

    /**
     * Get accessibility attributes specific to text.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Add role for semantic elements
        if ($this->element === 'mark') {
            $attributes['role'] = 'mark';
        }
        
        return $attributes;
    }

    /**
     * Override size classes since we handle text sizing differently.
     */
    protected function getSizeClasses(): array
    {
        // Size is handled by textSize, not by Bulma size classes
        return [];
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        // Skip parent validation for size since we handle it differently
        $validVariants = $this->getValidVariants();
        if (!empty($validVariants) && !in_array($this->variant, $validVariants)) {
            throw new \InvalidArgumentException(
                "Invalid variant '{$this->variant}'. Valid variants: " . implode(', ', $validVariants)
            );
        }

        $validElements = $this->getValidElements();
        if (!in_array($this->element, $validElements)) {
            throw new \InvalidArgumentException(
                "Invalid element '{$this->element}'. Valid elements: " . implode(', ', $validElements)
            );
        }

        $validTextSizes = $this->getValidTextSizes();
        if (!in_array($this->textSize, $validTextSizes)) {
            throw new \InvalidArgumentException(
                "Invalid text size '{$this->textSize}'. Valid sizes: " . implode(', ', $validTextSizes)
            );
        }

        $validWeights = $this->getValidWeights();
        if (!in_array($this->weight, $validWeights)) {
            throw new \InvalidArgumentException(
                "Invalid weight '{$this->weight}'. Valid weights: " . implode(', ', $validWeights)
            );
        }

        if ($this->color) {
            $validColors = $this->getValidColors();
            if (!in_array($this->color, $validColors)) {
                throw new \InvalidArgumentException(
                    "Invalid color '{$this->color}'. Valid colors: " . implode(', ', $validColors)
                );
            }
        }

        $validAlignments = $this->getValidAlignments();
        if (!in_array($this->align, $validAlignments)) {
            throw new \InvalidArgumentException(
                "Invalid alignment '{$this->align}'. Valid alignments: " . implode(', ', $validAlignments)
            );
        }

        if ($this->lines < 1) {
            throw new \InvalidArgumentException(
                "Lines must be at least 1, got '{$this->lines}'"
            );
        }
    }
}