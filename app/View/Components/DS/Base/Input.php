<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Input extends BaseComponent
{
    public string $type;
    public ?string $name;
    public ?string $id;
    public ?string $value;
    public ?string $placeholder;
    public bool $required;
    public bool $readonly;
    public ?string $pattern;
    public ?int $minLength;
    public ?int $maxLength;
    public ?string $min;
    public ?string $max;
    public ?string $step;
    public ?string $autocomplete;
    public ?string $label;
    public ?string $helpText;
    public ?string $errorMessage;
    public bool $hasIcon;
    public ?string $iconLeft;
    public ?string $iconRight;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $type = 'text',
        ?string $name = null,
        ?string $id = null,
        ?string $value = null,
        ?string $placeholder = null,
        bool $required = false,
        bool $readonly = false,
        ?string $pattern = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $min = null,
        ?string $max = null,
        ?string $step = null,
        ?string $autocomplete = null,
        ?string $label = null,
        ?string $helpText = null,
        ?string $errorMessage = null,
        bool $hasIcon = false,
        ?string $iconLeft = null,
        ?string $iconRight = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->type = $type;
        $this->name = $name;
        $this->id = $id ?: ($name ? "input_{$name}" : uniqid('input_'));
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->readonly = $readonly;
        $this->pattern = $pattern;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
        $this->autocomplete = $autocomplete;
        $this->label = $label;
        $this->helpText = $helpText;
        $this->errorMessage = $errorMessage;
        $this->hasIcon = $hasIcon || $iconLeft || $iconRight;
        $this->iconLeft = $iconLeft;
        $this->iconRight = $iconRight;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.input');
    }

    /**
     * Get base CSS classes specific to input component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['input', 'ds-component', 'ds-input'];
        
        // Add state classes
        if ($this->hasError()) {
            $classes[] = 'is-danger';
        }
        
        if ($this->loading) {
            $classes[] = 'is-loading';
        }
        
        return $classes;
    }

    /**
     * Get field wrapper CSS classes.
     */
    public function getFieldClasses(): string
    {
        $classes = ['field'];
        
        if ($this->hasIcon) {
            $classes[] = 'has-icons-left';
            if ($this->iconRight) {
                $classes[] = 'has-icons-right';
            }
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get control wrapper CSS classes.
     */
    public function getControlClasses(): string
    {
        $classes = ['control'];
        
        if ($this->loading) {
            $classes[] = 'is-loading';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get input-specific attributes.
     */
    public function getInputAttributes(): array
    {
        $attributes = [
            'type' => $this->type,
            'id' => $this->id,
        ];
        
        if ($this->name) {
            $attributes['name'] = $this->name;
        }
        
        if ($this->value !== null) {
            $attributes['value'] = $this->value;
        }
        
        if ($this->placeholder) {
            $attributes['placeholder'] = $this->placeholder;
        }
        
        if ($this->required) {
            $attributes['required'] = true;
        }
        
        if ($this->readonly) {
            $attributes['readonly'] = true;
        }
        
        if ($this->disabled) {
            $attributes['disabled'] = true;
        }
        
        if ($this->pattern) {
            $attributes['pattern'] = $this->pattern;
        }
        
        if ($this->minLength) {
            $attributes['minlength'] = $this->minLength;
        }
        
        if ($this->maxLength) {
            $attributes['maxlength'] = $this->maxLength;
        }
        
        if ($this->min !== null) {
            $attributes['min'] = $this->min;
        }
        
        if ($this->max !== null) {
            $attributes['max'] = $this->max;
        }
        
        if ($this->step !== null) {
            $attributes['step'] = $this->step;
        }
        
        if ($this->autocomplete) {
            $attributes['autocomplete'] = $this->autocomplete;
        }
        
        return $attributes;
    }

    /**
     * Get all component attributes including input-specific ones.
     */
    public function getComponentAttributes(): array
    {
        return array_merge(
            ['class' => $this->getBulmaClasses()],
            $this->getInputAttributes(),
            $this->getAlpineAttributes(),
            $this->getHtmxAttributes(),
            $this->getAccessibilityAttributes()
        );
    }

    /**
     * Check if the input has an error state.
     */
    public function hasError(): bool
    {
        return !empty($this->errorMessage);
    }

    /**
     * Check if the input has help text.
     */
    public function hasHelp(): bool
    {
        return !empty($this->helpText);
    }

    /**
     * Get valid input types.
     */
    protected function getValidTypes(): array
    {
        return [
            'text', 'email', 'password', 'number', 'tel', 'url', 'search',
            'date', 'datetime-local', 'month', 'time', 'week',
            'color', 'range', 'hidden'
        ];
    }

    /**
     * Get valid variants for input component.
     */
    protected function getValidVariants(): array
    {
        return ['primary', 'success', 'warning', 'danger', 'info'];
    }

    /**
     * Get accessibility attributes specific to inputs.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Associate with label
        if ($this->label) {
            $attributes['aria-labelledby'] = $this->id . '_label';
        }
        
        // Associate with help text
        if ($this->hasHelp()) {
            $attributes['aria-describedby'] = $this->id . '_help';
        }
        
        // Associate with error message
        if ($this->hasError()) {
            $attributes['aria-describedby'] = $this->id . '_error';
            $attributes['aria-invalid'] = 'true';
        }
        
        if ($this->required) {
            $attributes['aria-required'] = 'true';
        }
        
        return $attributes;
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        // Validate input type
        if (!in_array($this->type, $this->getValidTypes())) {
            throw new \InvalidArgumentException(
                "Invalid input type '{$this->type}'. Valid types: " . implode(', ', $this->getValidTypes())
            );
        }
    }
}