<?php

namespace App\View\Components\DS;

use Illuminate\View\Component;
use Illuminate\Support\Str;

abstract class BaseComponent extends Component
{
    public string $variant;
    public string $size;
    public bool $disabled;
    public bool $loading;
    public array $alpineData;
    public ?string $htmxAction;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null
    ) {
        $this->variant = $variant;
        $this->size = $size;
        $this->disabled = $disabled;
        $this->loading = $loading;
        $this->alpineData = $alpineData;
        $this->htmxAction = $htmxAction;
    }

    /**
     * Get the component name for CSS classes and Alpine.js registration.
     */
    protected function getComponentName(): string
    {
        $className = class_basename(static::class);
        return Str::kebab($className);
    }

    /**
     * Generate Bulma CSS classes for the component.
     */
    public function getBulmaClasses(): string
    {
        $baseClasses = $this->getBaseClasses();
        $variantClasses = $this->getVariantClasses();
        $sizeClasses = $this->getSizeClasses();
        $stateClasses = $this->getStateClasses();

        return collect([
            ...$baseClasses,
            ...$variantClasses,
            ...$sizeClasses,
            ...$stateClasses,
        ])->filter()->implode(' ');
    }

    /**
     * Get base CSS classes specific to the component.
     */
    protected function getBaseClasses(): array
    {
        return ['ds-component', "ds-{$this->getComponentName()}"];
    }

    /**
     * Get variant-specific CSS classes.
     */
    protected function getVariantClasses(): array
    {
        if (!$this->variant) return [];
        
        return [
            "is-{$this->variant}",
            "ds-{$this->getComponentName()}--{$this->variant}"
        ];
    }

    /**
     * Get size-specific CSS classes.
     */
    protected function getSizeClasses(): array
    {
        if (!$this->size || $this->size === 'normal') return [];
        
        return ["is-{$this->size}"];
    }

    /**
     * Get state-specific CSS classes.
     */
    protected function getStateClasses(): array
    {
        $classes = [];
        
        if ($this->loading) {
            $classes[] = 'is-loading';
        }
        
        if ($this->disabled) {
            $classes[] = 'is-disabled';
        }
        
        return $classes;
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        if (empty($this->alpineData)) {
            return [];
        }

        $componentName = $this->getComponentName();
        $config = json_encode($this->alpineData);
        
        return [
            'x-data' => "DS.component.{$componentName}({$config})"
        ];
    }

    /**
     * Generate HTMX attributes for the component.
     */
    public function getHtmxAttributes(): array
    {
        if (!$this->htmxAction) {
            return [];
        }

        $componentName = $this->getComponentName();
        
        return [
            'hx-post' => "/api/ds/{$componentName}/{$this->htmxAction}",
            'hx-indicator' => '.ds-loading',
            'hx-swap' => 'outerHTML',
            'hx-target' => 'this'
        ];
    }

    /**
     * Generate all component attributes.
     */
    public function getComponentAttributes(): array
    {
        return array_merge(
            ['class' => $this->getBulmaClasses()],
            $this->getAlpineAttributes(),
            $this->getHtmxAttributes(),
            $this->getAccessibilityAttributes()
        );
    }

    /**
     * Generate accessibility attributes.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = [];
        
        if ($this->disabled) {
            $attributes['disabled'] = true;
            $attributes['aria-disabled'] = 'true';
        }
        
        if ($this->loading) {
            $attributes['aria-busy'] = 'true';
        }
        
        return $attributes;
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        $validVariants = $this->getValidVariants();
        if (!empty($validVariants) && !in_array($this->variant, $validVariants)) {
            throw new \InvalidArgumentException(
                "Invalid variant '{$this->variant}'. Valid variants: " . implode(', ', $validVariants)
            );
        }

        $validSizes = $this->getValidSizes();
        if (!empty($validSizes) && !in_array($this->size, $validSizes)) {
            throw new \InvalidArgumentException(
                "Invalid size '{$this->size}'. Valid sizes: " . implode(', ', $validSizes)
            );
        }
    }

    /**
     * Get valid variants for this component.
     */
    protected function getValidVariants(): array
    {
        return ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'];
    }

    /**
     * Get valid sizes for this component.
     */
    protected function getValidSizes(): array
    {
        return ['small', 'normal', 'medium', 'large'];
    }

    /**
     * Boot the component (called after construction).
     */
    public function boot(): void
    {
        $this->validateProps();
    }
}