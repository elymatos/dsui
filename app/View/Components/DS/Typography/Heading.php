<?php

namespace App\View\Components\DS\Typography;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Heading extends BaseComponent
{
    public string $level;
    public string $weight;
    public ?string $color;
    public bool $subtitle;
    public string $align;
    public ?string $tag;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $level = '1',
        string $weight = 'normal',
        ?string $color = null,
        bool $subtitle = false,
        string $align = 'left',
        ?string $tag = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->level = $level;
        $this->weight = $weight;
        $this->color = $color;
        $this->subtitle = $subtitle;
        $this->align = $align;
        $this->tag = $tag;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.typography.heading');
    }

    /**
     * Get the HTML tag name for the heading.
     */
    public function getTagName(): string
    {
        if ($this->tag) {
            return $this->tag;
        }
        
        return $this->subtitle ? 'p' : "h{$this->level}";
    }

    /**
     * Get base CSS classes specific to heading component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-heading'];
        
        // Bulma heading classes
        if ($this->subtitle) {
            $classes[] = 'subtitle';
            if ($this->level !== '1') {
                $classes[] = "is-{$this->level}";
            }
        } else {
            $classes[] = 'title';
            if ($this->level !== '1') {
                $classes[] = "is-{$this->level}";
            }
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
        
        return $classes;
    }

    /**
     * Get valid heading levels.
     */
    public function getValidLevels(): array
    {
        return ['1', '2', '3', '4', '5', '6'];
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
     * Get valid HTML tags.
     */
    public function getValidTags(): array
    {
        return ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'];
    }

    /**
     * Get accessibility attributes specific to heading.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Add heading level for screen readers if using non-semantic tag
        if ($this->tag && !str_starts_with($this->tag, 'h')) {
            $attributes['role'] = 'heading';
            $attributes['aria-level'] = $this->level;
        }
        
        return $attributes;
    }

    /**
     * Get component size mapping for headings.
     */
    protected function getValidSizes(): array
    {
        return ['1', '2', '3', '4', '5', '6'];
    }

    /**
     * Override size classes to use heading levels instead.
     */
    protected function getSizeClasses(): array
    {
        // Size is handled by level, not by Bulma size classes
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

        $validLevels = $this->getValidLevels();
        if (!in_array($this->level, $validLevels)) {
            throw new \InvalidArgumentException(
                "Invalid level '{$this->level}'. Valid levels: " . implode(', ', $validLevels)
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

        if ($this->tag) {
            $validTags = $this->getValidTags();
            if (!in_array($this->tag, $validTags)) {
                throw new \InvalidArgumentException(
                    "Invalid tag '{$this->tag}'. Valid tags: " . implode(', ', $validTags)
                );
            }
        }
    }
}