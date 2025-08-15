<?php

namespace App\View\Components\DS\Layout;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Grid extends BaseComponent
{
    public int $columns;
    public string $gap;
    public bool $responsive;
    public string $alignment;
    public string $justification;
    public string $direction;
    public bool $autoFit;
    public int $minColumnWidth;
    public ?string $template;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        int $columns = 12,
        string $gap = 'normal',
        bool $responsive = true,
        string $alignment = 'stretch',
        string $justification = 'start',
        string $direction = 'row',
        bool $autoFit = false,
        int $minColumnWidth = 250,
        ?string $template = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->columns = $columns;
        $this->gap = $gap;
        $this->responsive = $responsive;
        $this->alignment = $alignment;
        $this->justification = $justification;
        $this->direction = $direction;
        $this->autoFit = $autoFit;
        $this->minColumnWidth = $minColumnWidth;
        $this->template = $template;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.layout.grid');
    }

    /**
     * Get base CSS classes specific to grid component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-grid'];
        
        // Add Bulma columns class
        $classes[] = 'columns';
        
        // Gap management
        if ($this->gap !== 'normal') {
            $classes[] = "ds-grid--gap-{$this->gap}";
        }
        
        // Responsive behavior
        if ($this->responsive) {
            $classes[] = 'ds-grid--responsive';
        } else {
            $classes[] = 'is-desktop';
        }
        
        // Alignment
        if ($this->alignment !== 'stretch') {
            $classes[] = "is-{$this->alignment}";
        }
        
        // Justification
        if ($this->justification !== 'start') {
            $classes[] = "is-{$this->justification}";
        }
        
        // Direction
        if ($this->direction === 'column') {
            $classes[] = 'ds-grid--column';
        }
        
        // Auto-fit behavior
        if ($this->autoFit) {
            $classes[] = 'ds-grid--auto-fit';
        }
        
        // Custom template
        if ($this->template) {
            $classes[] = "ds-grid--template-{$this->template}";
        }
        
        return $classes;
    }

    /**
     * Get CSS custom properties for the grid.
     */
    public function getCssVariables(): array
    {
        $variables = [];
        
        $variables['--ds-grid-columns'] = $this->columns;
        $variables['--ds-grid-min-column-width'] = $this->minColumnWidth . 'px';
        
        // Gap sizes
        $gapSizes = [
            'none' => '0',
            'small' => '0.75rem',
            'normal' => '1.5rem',
            'medium' => '2rem',
            'large' => '3rem'
        ];
        
        if (isset($gapSizes[$this->gap])) {
            $variables['--ds-grid-gap'] = $gapSizes[$this->gap];
        }
        
        return $variables;
    }

    /**
     * Get valid gap options.
     */
    public function getValidGaps(): array
    {
        return ['none', 'small', 'normal', 'medium', 'large'];
    }

    /**
     * Get valid alignment options.
     */
    public function getValidAlignments(): array
    {
        return ['start', 'center', 'end', 'stretch', 'baseline'];
    }

    /**
     * Get valid justification options.
     */
    public function getValidJustifications(): array
    {
        return ['start', 'center', 'end', 'space-between', 'space-around', 'space-evenly'];
    }

    /**
     * Get valid direction options.
     */
    public function getValidDirections(): array
    {
        return ['row', 'column'];
    }

    /**
     * Get valid template options.
     */
    public function getValidTemplates(): array
    {
        return ['sidebar', 'header-main-footer', 'two-column', 'three-column', 'masonry'];
    }

    /**
     * Check if using CSS Grid instead of Flexbox.
     */
    public function usesCssGrid(): bool
    {
        return $this->autoFit || $this->template || $this->columns > 12;
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        if ($this->columns < 1 || $this->columns > 24) {
            throw new \InvalidArgumentException(
                "Invalid columns '{$this->columns}'. Must be between 1 and 24."
            );
        }
        
        $validGaps = $this->getValidGaps();
        if (!in_array($this->gap, $validGaps)) {
            throw new \InvalidArgumentException(
                "Invalid gap '{$this->gap}'. Valid options: " . implode(', ', $validGaps)
            );
        }
        
        $validAlignments = $this->getValidAlignments();
        if (!in_array($this->alignment, $validAlignments)) {
            throw new \InvalidArgumentException(
                "Invalid alignment '{$this->alignment}'. Valid options: " . implode(', ', $validAlignments)
            );
        }
        
        $validJustifications = $this->getValidJustifications();
        if (!in_array($this->justification, $validJustifications)) {
            throw new \InvalidArgumentException(
                "Invalid justification '{$this->justification}'. Valid options: " . implode(', ', $validJustifications)
            );
        }
        
        $validDirections = $this->getValidDirections();
        if (!in_array($this->direction, $validDirections)) {
            throw new \InvalidArgumentException(
                "Invalid direction '{$this->direction}'. Valid options: " . implode(', ', $validDirections)
            );
        }
        
        if ($this->template) {
            $validTemplates = $this->getValidTemplates();
            if (!in_array($this->template, $validTemplates)) {
                throw new \InvalidArgumentException(
                    "Invalid template '{$this->template}'. Valid options: " . implode(', ', $validTemplates)
                );
            }
        }
        
        if ($this->minColumnWidth < 100 || $this->minColumnWidth > 1000) {
            throw new \InvalidArgumentException(
                "Invalid min column width '{$this->minColumnWidth}'. Must be between 100 and 1000 pixels."
            );
        }
    }
}