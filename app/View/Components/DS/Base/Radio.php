<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Radio extends BaseComponent
{
    public string $name;
    public mixed $value;
    public mixed $selectedValue;
    public ?string $label;
    public ?string $description;
    public string $position;
    public string $labelSize;
    public array $options;
    public string $layout;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $name = '',
        mixed $value = null,
        mixed $selectedValue = null,
        ?string $label = null,
        ?string $description = null,
        string $position = 'left',
        string $labelSize = 'normal',
        array $options = [],
        string $layout = 'vertical'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->name = $name;
        $this->value = $value;
        $this->selectedValue = $selectedValue;
        $this->label = $label;
        $this->description = $description;
        $this->position = $position;
        $this->labelSize = $labelSize;
        $this->options = $options;
        $this->layout = $layout;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.radio');
    }

    /**
     * Get base CSS classes specific to radio component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-radio'];
        
        if ($this->position === 'right') {
            $classes[] = 'ds-radio--right';
        }
        
        if ($this->labelSize !== 'normal') {
            $classes[] = "ds-radio--{$this->labelSize}";
        }
        
        if ($this->layout === 'horizontal') {
            $classes[] = 'ds-radio--horizontal';
        }
        
        return $classes;
    }

    /**
     * Get radio input classes.
     */
    public function getInputClasses(): string
    {
        $classes = ['ds-radio-input'];
        
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
        $classes = ['ds-radio-label'];
        
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
        $classes = ['ds-radio-wrapper'];
        
        if ($this->position === 'right') {
            $classes[] = 'is-right';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get group classes.
     */
    public function getGroupClasses(): string
    {
        $classes = ['ds-radio-group'];
        
        if ($this->layout === 'horizontal') {
            $classes[] = 'is-horizontal';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Check if this radio option is selected.
     */
    public function isSelected($optionValue = null): bool
    {
        $checkValue = $optionValue ?? $this->value;
        return $this->selectedValue == $checkValue;
    }

    /**
     * Get formatted options for the radio group.
     */
    public function getFormattedOptions(): array
    {
        return collect($this->options)->map(function ($option, $key) {
            if (is_array($option)) {
                return [
                    'value' => $option['value'] ?? $key,
                    'label' => $option['label'] ?? $option['value'] ?? $key,
                    'description' => $option['description'] ?? null,
                    'disabled' => $option['disabled'] ?? false,
                ];
            }
            
            return [
                'value' => $key,
                'label' => $option,
                'description' => null,
                'disabled' => false,
            ];
        })->values()->toArray();
    }

    /**
     * Get valid variants for radio component.
     */
    protected function getValidVariants(): array
    {
        return [
            'primary', 'secondary', 'success', 'warning', 'danger', 'info'
        ];
    }

    /**
     * Get valid sizes for radio component.
     */
    protected function getValidSizes(): array
    {
        return ['small', 'normal', 'medium', 'large'];
    }

    /**
     * Get valid positions for radio component.
     */
    public function getValidPositions(): array
    {
        return ['left', 'right'];
    }

    /**
     * Get valid layouts for radio component.
     */
    public function getValidLayouts(): array
    {
        return ['vertical', 'horizontal'];
    }

    /**
     * Get accessibility attributes specific to radio.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        $attributes['role'] = 'radiogroup';
        
        if ($this->description) {
            $attributes['aria-describedby'] = $this->getDescriptionId();
        }
        
        return $attributes;
    }

    /**
     * Get unique ID for the radio group.
     */
    public function getRadioGroupId(): string
    {
        return 'radio-group-' . uniqid();
    }

    /**
     * Get unique ID for a radio option.
     */
    public function getRadioId($value): string
    {
        return 'radio-' . $this->name . '-' . $value . '-' . uniqid();
    }

    /**
     * Get unique ID for the description.
     */
    public function getDescriptionId(): string
    {
        return 'radio-desc-' . uniqid();
    }

    /**
     * Get Alpine.js configuration for the radio component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'selectedValue' => $this->selectedValue,
            'name' => $this->name,
            'disabled' => $this->disabled,
            'options' => $this->getFormattedOptions(),
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.radio({$config})"
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
        
        $validLayouts = $this->getValidLayouts();
        if (!in_array($this->layout, $validLayouts)) {
            throw new \InvalidArgumentException(
                "Invalid layout '{$this->layout}'. Valid layouts: " . implode(', ', $validLayouts)
            );
        }
    }
}