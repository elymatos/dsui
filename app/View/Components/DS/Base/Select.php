<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Select extends BaseComponent
{
    public array $options;
    public bool $multiple;
    public bool $searchable;
    public ?string $placeholder;
    public string $name;
    public mixed $value;
    public bool $clearable;
    public int $maxHeight;
    public ?string $searchPlaceholder;
    public ?string $label;
    public ?string $helpText;
    public ?string $errorMessage;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        array $options = [],
        bool $multiple = false,
        bool $searchable = false,
        ?string $placeholder = null,
        string $name = '',
        mixed $value = null,
        bool $clearable = false,
        int $maxHeight = 200,
        ?string $searchPlaceholder = 'Search options...',
        ?string $label = null,
        ?string $helpText = null,
        ?string $errorMessage = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->options = $options;
        $this->multiple = $multiple;
        $this->searchable = $searchable;
        $this->placeholder = $placeholder ?: ($multiple ? 'Select options...' : 'Select an option...');
        $this->name = $name;
        $this->value = $value;
        $this->clearable = $clearable;
        $this->maxHeight = $maxHeight;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->label = $label;
        $this->helpText = $helpText;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.select');
    }

    /**
     * Get base CSS classes specific to select component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['select', 'ds-component', 'ds-select'];
        
        if ($this->multiple) {
            $classes[] = 'is-multiple';
        }
        
        if ($this->loading) {
            $classes[] = 'is-loading';
        }
        
        return $classes;
    }

    /**
     * Get the formatted options for the select.
     */
    public function getFormattedOptions(): array
    {
        return collect($this->options)->map(function ($option, $key) {
            if (is_array($option)) {
                return [
                    'value' => $option['value'] ?? $key,
                    'label' => $option['label'] ?? $option['value'] ?? $key,
                    'disabled' => $option['disabled'] ?? false,
                    'selected' => $this->isSelected($option['value'] ?? $key),
                ];
            }
            
            return [
                'value' => $key,
                'label' => $option,
                'disabled' => false,
                'selected' => $this->isSelected($key),
            ];
        })->values()->toArray();
    }

    /**
     * Check if a value is selected.
     */
    public function isSelected($optionValue): bool
    {
        if ($this->multiple) {
            return is_array($this->value) && in_array($optionValue, $this->value);
        }
        
        return $this->value == $optionValue;
    }

    /**
     * Get the display value for the select.
     */
    public function getDisplayValue(): string
    {
        if (empty($this->value)) {
            return $this->placeholder;
        }
        
        if ($this->multiple) {
            if (!is_array($this->value) || empty($this->value)) {
                return $this->placeholder;
            }
            
            $selectedLabels = collect($this->getFormattedOptions())
                ->whereIn('value', $this->value)
                ->pluck('label')
                ->toArray();
                
            return count($selectedLabels) > 2 
                ? count($selectedLabels) . ' options selected'
                : implode(', ', $selectedLabels);
        }
        
        $selectedOption = collect($this->getFormattedOptions())
            ->firstWhere('value', $this->value);
            
        return $selectedOption['label'] ?? $this->placeholder;
    }

    /**
     * Get valid variants for select component.
     */
    protected function getValidVariants(): array
    {
        return [
            'primary', 'secondary', 'success', 'warning', 'danger', 'info'
        ];
    }

    /**
     * Get accessibility attributes specific to select.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        $attributes['role'] = 'listbox';
        
        if ($this->multiple) {
            $attributes['aria-multiselectable'] = 'true';
        }
        
        if ($this->searchable) {
            $attributes['aria-haspopup'] = 'listbox';
            $attributes['aria-expanded'] = 'false';
        }
        
        return $attributes;
    }

    /**
     * Get Alpine.js configuration for the select component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'options' => $this->getFormattedOptions(),
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
            'clearable' => $this->clearable,
            'placeholder' => $this->placeholder,
            'searchPlaceholder' => $this->searchPlaceholder,
            'maxHeight' => $this->maxHeight,
            'value' => $this->value,
            'disabled' => $this->disabled,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.select({$config})"
        ];
    }
}