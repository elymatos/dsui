<?php

namespace App\View\Components\DS\Complex;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Dropdown extends BaseComponent
{
    public array $options;
    public mixed $value;
    public bool $multiple;
    public bool $searchable;
    public string $searchPlaceholder;
    public bool $clearable;
    public string $placeholder;
    public bool $creatable;
    public int $maxItems;
    public bool $grouping;
    public bool $tagging;
    public string $position;
    public bool $virtualized;
    public int $virtualItemHeight;
    public bool $asyncSearch;
    public ?string $searchUrl;
    public int $minSearchLength;
    public int $searchDelay;
    public bool $closeOnSelect;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        array $options = [],
        mixed $value = null,
        bool $multiple = false,
        bool $searchable = false,
        string $searchPlaceholder = 'Search options...',
        bool $clearable = true,
        string $placeholder = 'Select an option...',
        bool $creatable = false,
        int $maxItems = 0,
        bool $grouping = false,
        bool $tagging = false,
        string $position = 'bottom',
        bool $virtualized = false,
        int $virtualItemHeight = 32,
        bool $asyncSearch = false,
        ?string $searchUrl = null,
        int $minSearchLength = 2,
        int $searchDelay = 300,
        bool $closeOnSelect = true
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->options = $options;
        $this->value = $value;
        $this->multiple = $multiple;
        $this->searchable = $searchable;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->clearable = $clearable;
        $this->placeholder = $placeholder;
        $this->creatable = $creatable;
        $this->maxItems = $maxItems;
        $this->grouping = $grouping;
        $this->tagging = $tagging;
        $this->position = $position;
        $this->virtualized = $virtualized;
        $this->virtualItemHeight = $virtualItemHeight;
        $this->asyncSearch = $asyncSearch;
        $this->searchUrl = $searchUrl;
        $this->minSearchLength = $minSearchLength;
        $this->searchDelay = $searchDelay;
        $this->closeOnSelect = $closeOnSelect;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.complex.dropdown');
    }

    /**
     * Get base CSS classes specific to dropdown component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-dropdown'];
        
        // Multiple selection
        if ($this->multiple) {
            $classes[] = 'ds-dropdown--multiple';
        }
        
        // Searchable
        if ($this->searchable) {
            $classes[] = 'ds-dropdown--searchable';
        }
        
        // Clearable
        if ($this->clearable) {
            $classes[] = 'ds-dropdown--clearable';
        }
        
        // Creatable
        if ($this->creatable) {
            $classes[] = 'ds-dropdown--creatable';
        }
        
        // Tagging
        if ($this->tagging) {
            $classes[] = 'ds-dropdown--tagging';
        }
        
        // Position
        $classes[] = "ds-dropdown--{$this->position}";
        
        // Virtualized
        if ($this->virtualized) {
            $classes[] = 'ds-dropdown--virtualized';
        }
        
        return $classes;
    }

    /**
     * Get formatted options data.
     */
    public function getFormattedOptions(): array
    {
        return collect($this->options)->map(function ($option, $index) {
            if (is_array($option)) {
                return [
                    'value' => $option['value'] ?? $index,
                    'label' => $option['label'] ?? $option['value'] ?? $index,
                    'description' => $option['description'] ?? null,
                    'icon' => $option['icon'] ?? null,
                    'avatar' => $option['avatar'] ?? null,
                    'disabled' => $option['disabled'] ?? false,
                    'group' => $option['group'] ?? null,
                    'data' => $option['data'] ?? [],
                ];
            } else {
                return [
                    'value' => $index,
                    'label' => $option,
                    'description' => null,
                    'icon' => null,
                    'avatar' => null,
                    'disabled' => false,
                    'group' => null,
                    'data' => [],
                ];
            }
        })->toArray();
    }

    /**
     * Get formatted value.
     */
    public function getFormattedValue(): mixed
    {
        if ($this->multiple) {
            return is_array($this->value) ? $this->value : ($this->value ? [$this->value] : []);
        }
        
        return $this->value;
    }

    /**
     * Get valid positions.
     */
    public function getValidPositions(): array
    {
        return ['top', 'bottom', 'auto'];
    }

    /**
     * Get Alpine.js configuration for the dropdown component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'options' => $this->getFormattedOptions(),
            'value' => $this->getFormattedValue(),
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
            'searchPlaceholder' => $this->searchPlaceholder,
            'clearable' => $this->clearable,
            'placeholder' => $this->placeholder,
            'creatable' => $this->creatable,
            'maxItems' => $this->maxItems,
            'grouping' => $this->grouping,
            'tagging' => $this->tagging,
            'position' => $this->position,
            'virtualized' => $this->virtualized,
            'virtualItemHeight' => $this->virtualItemHeight,
            'asyncSearch' => $this->asyncSearch,
            'searchUrl' => $this->searchUrl,
            'minSearchLength' => $this->minSearchLength,
            'searchDelay' => $this->searchDelay,
            'closeOnSelect' => $this->closeOnSelect,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.dropdown({$config})"
        ];
    }

    /**
     * Get unique ID for dropdown.
     */
    public function getDropdownId(): string
    {
        return 'dropdown-' . uniqid();
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
        
        if ($this->maxItems < 0) {
            throw new \InvalidArgumentException('maxItems must be non-negative (0 = unlimited)');
        }
        
        if ($this->minSearchLength < 0) {
            throw new \InvalidArgumentException('minSearchLength must be non-negative');
        }
        
        if ($this->searchDelay < 0) {
            throw new \InvalidArgumentException('searchDelay must be non-negative');
        }
        
        if ($this->virtualItemHeight < 1) {
            throw new \InvalidArgumentException('virtualItemHeight must be at least 1');
        }
        
        if ($this->asyncSearch && !$this->searchUrl) {
            throw new \InvalidArgumentException('searchUrl is required when asyncSearch is enabled');
        }
    }
}