<?php

namespace App\View\Components\DS\Layout;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Container extends BaseComponent
{
    public string $maxWidth;
    public bool $fluid;
    public bool $centered;
    public string $padding;
    public string $margin;
    public ?string $breakpoint;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $maxWidth = 'desktop',
        bool $fluid = false,
        bool $centered = true,
        string $padding = 'normal',
        string $margin = 'none',
        ?string $breakpoint = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->maxWidth = $maxWidth;
        $this->fluid = $fluid;
        $this->centered = $centered;
        $this->padding = $padding;
        $this->margin = $margin;
        $this->breakpoint = $breakpoint;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.layout.container');
    }

    /**
     * Get base CSS classes specific to container component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-container'];
        
        // Add Bulma container class
        if ($this->fluid) {
            $classes[] = 'container-fluid';
        } else {
            $classes[] = 'container';
        }
        
        // Max width constraints
        if (!$this->fluid) {
            switch ($this->maxWidth) {
                case 'widescreen':
                    $classes[] = 'is-widescreen';
                    break;
                case 'fullhd':
                    $classes[] = 'is-fullhd';
                    break;
                case 'max-desktop':
                    $classes[] = 'is-max-desktop';
                    break;
                case 'max-widescreen':
                    $classes[] = 'is-max-widescreen';
                    break;
                default:
                    // 'desktop' is default, no additional class needed
                    break;
            }
        }
        
        // Padding classes
        if ($this->padding !== 'none') {
            $classes[] = "ds-container--padding-{$this->padding}";
        }
        
        // Margin classes
        if ($this->margin !== 'none') {
            $classes[] = "ds-container--margin-{$this->margin}";
        }
        
        // Centering
        if (!$this->centered) {
            $classes[] = 'ds-container--no-center';
        }
        
        // Breakpoint-specific behavior
        if ($this->breakpoint) {
            $classes[] = "ds-container--from-{$this->breakpoint}";
        }
        
        return $classes;
    }

    /**
     * Get valid max width options.
     */
    public function getValidMaxWidths(): array
    {
        return ['desktop', 'widescreen', 'fullhd', 'max-desktop', 'max-widescreen'];
    }

    /**
     * Get valid padding options.
     */
    public function getValidPadding(): array
    {
        return ['none', 'small', 'normal', 'medium', 'large'];
    }

    /**
     * Get valid margin options.
     */
    public function getValidMargins(): array
    {
        return ['none', 'small', 'normal', 'medium', 'large', 'auto'];
    }

    /**
     * Get valid breakpoint options.
     */
    public function getValidBreakpoints(): array
    {
        return ['mobile', 'tablet', 'desktop', 'widescreen', 'fullhd'];
    }

    /**
     * Get container max-width value.
     */
    public function getMaxWidthValue(): string
    {
        return match($this->maxWidth) {
            'desktop' => '1024px',
            'widescreen' => '1216px',
            'fullhd' => '1408px',
            'max-desktop' => '1023px',
            'max-widescreen' => '1215px',
            default => '1024px'
        };
    }

    /**
     * Get inline styles for the container.
     */
    public function getInlineStyles(): array
    {
        $styles = [];
        
        if ($this->fluid) {
            $styles['width'] = '100%';
            $styles['max-width'] = 'none';
        }
        
        return $styles;
    }

    /**
     * Get CSS custom properties for the container.
     */
    public function getCssVariables(): array
    {
        $variables = [];
        
        if (!$this->fluid) {
            $variables['--ds-container-max-width'] = $this->getMaxWidthValue();
        }
        
        return $variables;
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validMaxWidths = $this->getValidMaxWidths();
        if (!in_array($this->maxWidth, $validMaxWidths)) {
            throw new \InvalidArgumentException(
                "Invalid max width '{$this->maxWidth}'. Valid options: " . implode(', ', $validMaxWidths)
            );
        }
        
        $validPadding = $this->getValidPadding();
        if (!in_array($this->padding, $validPadding)) {
            throw new \InvalidArgumentException(
                "Invalid padding '{$this->padding}'. Valid options: " . implode(', ', $validPadding)
            );
        }
        
        $validMargins = $this->getValidMargins();
        if (!in_array($this->margin, $validMargins)) {
            throw new \InvalidArgumentException(
                "Invalid margin '{$this->margin}'. Valid options: " . implode(', ', $validMargins)
            );
        }
        
        if ($this->breakpoint) {
            $validBreakpoints = $this->getValidBreakpoints();
            if (!in_array($this->breakpoint, $validBreakpoints)) {
                throw new \InvalidArgumentException(
                    "Invalid breakpoint '{$this->breakpoint}'. Valid options: " . implode(', ', $validBreakpoints)
                );
            }
        }
    }
}