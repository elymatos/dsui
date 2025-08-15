<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Button extends BaseComponent
{
    public string $type;
    public ?string $href;
    public bool $outlined;
    public bool $rounded;
    public bool $fullWidth;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $type = 'button',
        ?string $href = null,
        bool $outlined = false,
        bool $rounded = false,
        bool $fullWidth = false
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->type = $type;
        $this->href = $href;
        $this->outlined = $outlined;
        $this->rounded = $rounded;
        $this->fullWidth = $fullWidth;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.button');
    }

    /**
     * Get base CSS classes specific to button component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['button', 'ds-component', 'ds-button'];
        
        if ($this->outlined) {
            $classes[] = 'is-outlined';
        }
        
        if ($this->rounded) {
            $classes[] = 'is-rounded';
        }
        
        if ($this->fullWidth) {
            $classes[] = 'is-fullwidth';
        }
        
        return $classes;
    }

    /**
     * Determine if this should render as a link or button.
     */
    public function isLink(): bool
    {
        return !empty($this->href);
    }

    /**
     * Get the HTML tag name for the component.
     */
    public function getTagName(): string
    {
        return $this->isLink() ? 'a' : 'button';
    }

    /**
     * Get tag-specific attributes.
     */
    public function getTagAttributes(): array
    {
        $attributes = [];
        
        if ($this->isLink()) {
            $attributes['href'] = $this->href;
            if ($this->disabled) {
                $attributes['tabindex'] = '-1';
                $attributes['aria-disabled'] = 'true';
            }
        } else {
            $attributes['type'] = $this->type;
            if ($this->disabled) {
                $attributes['disabled'] = true;
            }
        }
        
        return $attributes;
    }

    /**
     * Get all component attributes including tag-specific ones.
     */
    public function getComponentAttributes(): array
    {
        return array_merge(
            parent::getComponentAttributes(),
            $this->getTagAttributes()
        );
    }

    /**
     * Get valid variants for button component.
     */
    protected function getValidVariants(): array
    {
        return [
            'primary', 'secondary', 'success', 'warning', 'danger', 'info', 
            'light', 'dark', 'link', 'text', 'white'
        ];
    }

    /**
     * Get accessibility attributes specific to buttons.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Add button-specific ARIA attributes
        if ($this->loading) {
            $attributes['aria-label'] = 'Loading';
        }
        
        return $attributes;
    }
}