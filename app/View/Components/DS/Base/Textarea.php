<?php

namespace App\View\Components\DS\Base;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Textarea extends BaseComponent
{
    public ?string $name;
    public ?string $id;
    public ?string $value;
    public ?string $placeholder;
    public bool $required;
    public bool $readonly;
    public ?int $rows;
    public ?int $cols;
    public ?int $minLength;
    public ?int $maxLength;
    public ?string $label;
    public ?string $helpText;
    public ?string $errorMessage;
    public bool $autoResize;
    public bool $showCounter;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        ?string $name = null,
        ?string $id = null,
        ?string $value = null,
        ?string $placeholder = null,
        bool $required = false,
        bool $readonly = false,
        ?int $rows = 4,
        ?int $cols = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $label = null,
        ?string $helpText = null,
        ?string $errorMessage = null,
        bool $autoResize = true,
        bool $showCounter = false
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->name = $name;
        $this->id = $id ?: ($name ? "textarea_{$name}" : uniqid('textarea_'));
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->readonly = $readonly;
        $this->rows = $rows;
        $this->cols = $cols;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->label = $label;
        $this->helpText = $helpText;
        $this->errorMessage = $errorMessage;
        $this->autoResize = $autoResize;
        $this->showCounter = $showCounter && $maxLength;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.base.textarea');
    }

    /**
     * Get base CSS classes specific to textarea component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['textarea', 'ds-component', 'ds-textarea'];
        
        // Add state classes
        if ($this->hasError()) {
            $classes[] = 'is-danger';
        }
        
        if ($this->loading) {
            $classes[] = 'is-loading';
        }

        if ($this->autoResize) {
            $classes[] = 'ds-textarea--auto-resize';
        }
        
        return $classes;
    }

    /**
     * Get field wrapper CSS classes.
     */
    public function getFieldClasses(): string
    {
        return 'field';
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
     * Get textarea-specific attributes.
     */
    public function getTextareaAttributes(): array
    {
        $attributes = [
            'id' => $this->id,
        ];
        
        if ($this->name) {
            $attributes['name'] = $this->name;
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
        
        if ($this->rows) {
            $attributes['rows'] = $this->rows;
        }
        
        if ($this->cols) {
            $attributes['cols'] = $this->cols;
        }
        
        if ($this->minLength) {
            $attributes['minlength'] = $this->minLength;
        }
        
        if ($this->maxLength) {
            $attributes['maxlength'] = $this->maxLength;
        }
        
        return $attributes;
    }

    /**
     * Get all component attributes including textarea-specific ones.
     */
    public function getComponentAttributes(): array
    {
        return array_merge(
            ['class' => $this->getBulmaClasses()],
            $this->getTextareaAttributes(),
            $this->getAlpineAttributes(),
            $this->getHtmxAttributes(),
            $this->getAccessibilityAttributes()
        );
    }

    /**
     * Check if the textarea has an error state.
     */
    public function hasError(): bool
    {
        return !empty($this->errorMessage);
    }

    /**
     * Check if the textarea has help text.
     */
    public function hasHelp(): bool
    {
        return !empty($this->helpText);
    }

    /**
     * Get accessibility attributes specific to textareas.
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
}