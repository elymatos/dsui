<?php

namespace App\View\Components\DS\Feedback;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Modal extends BaseComponent
{
    public ?string $title;
    public bool $closable;
    public bool $closeOnOverlay;
    public bool $closeOnEscape;
    public string $modalSize;
    public bool $scrollable;
    public bool $centered;
    public ?string $header;
    public ?string $footer;
    public bool $fullscreen;
    public string $animation;
    public ?string $trigger;
    public bool $persistent;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        ?string $title = null,
        bool $closable = true,
        bool $closeOnOverlay = true,
        bool $closeOnEscape = true,
        string $modalSize = 'normal',
        bool $scrollable = false,
        bool $centered = true,
        ?string $header = null,
        ?string $footer = null,
        bool $fullscreen = false,
        string $animation = 'fade',
        ?string $trigger = null,
        bool $persistent = false
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->title = $title;
        $this->closable = $closable;
        $this->closeOnOverlay = $closeOnOverlay;
        $this->closeOnEscape = $closeOnEscape;
        $this->modalSize = $modalSize;
        $this->scrollable = $scrollable;
        $this->centered = $centered;
        $this->header = $header;
        $this->footer = $footer;
        $this->fullscreen = $fullscreen;
        $this->animation = $animation;
        $this->trigger = $trigger;
        $this->persistent = $persistent;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.feedback.modal');
    }

    /**
     * Get base CSS classes specific to modal component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-modal', 'modal'];
        
        // Modal size
        if ($this->modalSize !== 'normal') {
            $classes[] = "ds-modal--{$this->modalSize}";
        }
        
        // Scrollable content
        if ($this->scrollable) {
            $classes[] = 'ds-modal--scrollable';
        }
        
        // Centered modal
        if ($this->centered) {
            $classes[] = 'ds-modal--centered';
        }
        
        // Fullscreen modal
        if ($this->fullscreen) {
            $classes[] = 'ds-modal--fullscreen';
        }
        
        // Animation type
        if ($this->animation !== 'fade') {
            $classes[] = "ds-modal--{$this->animation}";
        }
        
        // Persistent modal (non-dismissible)
        if ($this->persistent) {
            $classes[] = 'ds-modal--persistent';
        }
        
        return $classes;
    }

    /**
     * Get modal dialog classes.
     */
    public function getDialogClasses(): string
    {
        $classes = ['modal-card'];
        
        return implode(' ', $classes);
    }

    /**
     * Get valid modal sizes.
     */
    public function getValidModalSizes(): array
    {
        return ['small', 'normal', 'medium', 'large', 'extra-large', 'fullscreen'];
    }

    /**
     * Get valid animation types.
     */
    public function getValidAnimations(): array
    {
        return ['fade', 'slide-up', 'slide-down', 'slide-left', 'slide-right', 'zoom', 'flip'];
    }

    /**
     * Get accessibility attributes specific to modal.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        $attributes['role'] = 'dialog';
        $attributes['aria-modal'] = 'true';
        $attributes['tabindex'] = '-1';
        
        if ($this->title) {
            $attributes['aria-labelledby'] = $this->getTitleId();
        }
        
        if (!$this->title && !isset($attributes['aria-label'])) {
            $attributes['aria-label'] = 'Modal dialog';
        }
        
        return $attributes;
    }

    /**
     * Get unique ID for the modal title.
     */
    public function getTitleId(): string
    {
        return 'modal-title-' . uniqid();
    }

    /**
     * Get unique ID for the modal.
     */
    public function getModalId(): string
    {
        return 'modal-' . uniqid();
    }

    /**
     * Get Alpine.js configuration for the modal component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'open' => false,
            'closable' => $this->closable,
            'closeOnOverlay' => $this->closeOnOverlay,
            'closeOnEscape' => $this->closeOnEscape,
            'scrollable' => $this->scrollable,
            'centered' => $this->centered,
            'fullscreen' => $this->fullscreen,
            'animation' => $this->animation,
            'persistent' => $this->persistent,
            'trigger' => $this->trigger,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.modal({$config})",
            'x-show' => 'open',
            'x-cloak' => true
        ];
    }

    /**
     * Check if modal has custom header content.
     */
    public function hasCustomHeader(): bool
    {
        return !empty($this->header);
    }

    /**
     * Check if modal has custom footer content.
     */
    public function hasCustomFooter(): bool
    {
        return !empty($this->footer);
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validModalSizes = $this->getValidModalSizes();
        if (!in_array($this->modalSize, $validModalSizes)) {
            throw new \InvalidArgumentException(
                "Invalid modal size '{$this->modalSize}'. Valid sizes: " . implode(', ', $validModalSizes)
            );
        }
        
        $validAnimations = $this->getValidAnimations();
        if (!in_array($this->animation, $validAnimations)) {
            throw new \InvalidArgumentException(
                "Invalid animation '{$this->animation}'. Valid animations: " . implode(', ', $validAnimations)
            );
        }
    }
}