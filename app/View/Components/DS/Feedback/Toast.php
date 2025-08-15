<?php

namespace App\View\Components\DS\Feedback;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Toast extends BaseComponent
{
    public string $position;
    public int $maxToasts;
    public int $defaultDuration;
    public bool $pauseOnHover;
    public bool $clickToClose;
    public bool $swipeToClose;
    public bool $showProgress;
    public bool $stackable;
    public string $animation;

    public function __construct(
        string $variant = 'info',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $position = 'top-right',
        int $maxToasts = 5,
        int $defaultDuration = 5000,
        bool $pauseOnHover = true,
        bool $clickToClose = true,
        bool $swipeToClose = true,
        bool $showProgress = true,
        bool $stackable = true,
        string $animation = 'slide'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->position = $position;
        $this->maxToasts = $maxToasts;
        $this->defaultDuration = $defaultDuration;
        $this->pauseOnHover = $pauseOnHover;
        $this->clickToClose = $clickToClose;
        $this->swipeToClose = $swipeToClose;
        $this->showProgress = $showProgress;
        $this->stackable = $stackable;
        $this->animation = $animation;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.feedback.toast');
    }

    /**
     * Get base CSS classes specific to toast component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-toast-container'];
        
        // Position
        $classes[] = "ds-toast-container--{$this->position}";
        
        // Stackable
        if ($this->stackable) {
            $classes[] = 'ds-toast-container--stackable';
        }
        
        // Animation
        $classes[] = "ds-toast-container--{$this->animation}";
        
        return $classes;
    }

    /**
     * Get valid positions.
     */
    public function getValidPositions(): array
    {
        return [
            'top-left', 'top-center', 'top-right',
            'middle-left', 'middle-center', 'middle-right',
            'bottom-left', 'bottom-center', 'bottom-right'
        ];
    }

    /**
     * Get valid animations.
     */
    public function getValidAnimations(): array
    {
        return ['slide', 'fade', 'bounce', 'zoom'];
    }

    /**
     * Get Alpine.js configuration for the toast component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'position' => $this->position,
            'maxToasts' => $this->maxToasts,
            'defaultDuration' => $this->defaultDuration,
            'pauseOnHover' => $this->pauseOnHover,
            'clickToClose' => $this->clickToClose,
            'swipeToClose' => $this->swipeToClose,
            'showProgress' => $this->showProgress,
            'stackable' => $this->stackable,
            'animation' => $this->animation,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.toast({$config})"
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
        
        $validAnimations = $this->getValidAnimations();
        if (!in_array($this->animation, $validAnimations)) {
            throw new \InvalidArgumentException(
                "Invalid animation '{$this->animation}'. Valid animations: " . implode(', ', $validAnimations)
            );
        }
        
        if ($this->maxToasts < 1) {
            throw new \InvalidArgumentException('maxToasts must be at least 1');
        }
        
        if ($this->defaultDuration < 0) {
            throw new \InvalidArgumentException('defaultDuration must be non-negative (0 = never auto-hide)');
        }
    }
}