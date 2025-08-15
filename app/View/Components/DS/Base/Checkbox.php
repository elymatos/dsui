<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Checkbox extends BaseComponent
{
    public string $name;
    public mixed $value;
    public bool $checked;
    public bool $indeterminate;
    public ?string $label;
    public ?string $description;
    public string $position;
    public string $labelSize;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $name = '',
        mixed $value = true,
        bool $checked = false,
        bool $indeterminate = false,
        ?string $label = null,
        ?string $description = null,
        string $position = 'left',
        string $labelSize = 'normal'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->name = $name;
        $this->value = $value;
        $this->checked = $checked;
        $this->indeterminate = $indeterminate;
        $this->label = $label;
        $this->description = $description;
        $this->position = $position;
        $this->labelSize = $labelSize;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.checkbox');
    }

    /**
     * Get base CSS classes specific to checkbox component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-checkbox'];
        
        if ($this->position === 'right') {
            $classes[] = 'ds-checkbox--right';
        }
        
        if ($this->labelSize !== 'normal') {
            $classes[] = "ds-checkbox--{$this->labelSize}";
        }
        
        return $classes;
    }

    /**
     * Get checkbox input classes.
     */
    public function getInputClasses(): string
    {
        $classes = ['ds-checkbox-input'];
        
        if ($this->size !== 'normal') {
            $classes[] = "is-{$this->size}";
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get label classes.
     */
    public function getLabelClasses(): string
    {
        $classes = ['ds-checkbox-label'];
        
        if ($this->labelSize !== 'normal') {
            $classes[] = "is-{$this->labelSize}";
        }
        
        if ($this->disabled) {
            $classes[] = 'is-disabled';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get wrapper classes.
     */
    public function getWrapperClasses(): string
    {
        $classes = ['ds-checkbox-wrapper'];
        
        if ($this->position === 'right') {
            $classes[] = 'is-right';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get valid variants for checkbox component.
     */
    protected function getValidVariants(): array
    {
        return [
            'primary', 'secondary', 'success', 'warning', 'danger', 'info'
        ];
    }

    /**
     * Get valid sizes for checkbox component.
     */
    protected function getValidSizes(): array
    {
        return ['small', 'normal', 'medium', 'large'];
    }

    /**
     * Get valid positions for checkbox component.
     */
    public function getValidPositions(): array
    {
        return ['left', 'right'];
    }

    /**
     * Get accessibility attributes specific to checkbox.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        if ($this->indeterminate) {
            $attributes['aria-checked'] = 'mixed';
        } elseif ($this->checked) {
            $attributes['aria-checked'] = 'true';
        } else {
            $attributes['aria-checked'] = 'false';
        }
        
        if ($this->description) {
            $attributes['aria-describedby'] = $this->getDescriptionId();
        }
        
        return $attributes;
    }

    /**
     * Get unique ID for the checkbox.
     */
    public function getCheckboxId(): string
    {
        return 'checkbox-' . uniqid();
    }

    /**
     * Get unique ID for the description.
     */
    public function getDescriptionId(): string
    {
        return 'checkbox-desc-' . uniqid();
    }

    /**
     * Get Alpine.js configuration for the checkbox component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'checked' => $this->checked,
            'indeterminate' => $this->indeterminate,
            'value' => $this->value,
            'disabled' => $this->disabled,
            'name' => $this->name,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.checkbox({$config})"
        ];
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validPositions = $this->getValidPositions();
        if (!in_array($this->position, $validPositions)) {
            throw new \InvalidArgumentException(
                "Invalid position '{$this->position}'. Valid positions: " . implode(', ', $validPositions)
            );
        }
    }
}