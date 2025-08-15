<?php

namespace App\View\Components\DS\Feedback;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Tooltip extends BaseComponent
{
    public string $content;
    public string $position;
    public string $trigger;
    public int $delay;
    public int $hideDelay;
    public bool $arrow;
    public string $theme;
    public int $maxWidth;
    public bool $interactive;
    public bool $followCursor;
    public string $boundary;
    public int $offset;
    public bool $html;
    public bool $animation;
    public string $animationType;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $content = '',
        string $position = 'top',
        string $trigger = 'hover',
        int $delay = 0,
        int $hideDelay = 0,
        bool $arrow = true,
        string $theme = 'dark',
        int $maxWidth = 200,
        bool $interactive = false,
        bool $followCursor = false,
        string $boundary = 'viewport',
        int $offset = 8,
        bool $html = false,
        bool $animation = true,
        string $animationType = 'fade'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->content = $content;
        $this->position = $position;
        $this->trigger = $trigger;
        $this->delay = $delay;
        $this->hideDelay = $hideDelay;
        $this->arrow = $arrow;
        $this->theme = $theme;
        $this->maxWidth = $maxWidth;
        $this->interactive = $interactive;
        $this->followCursor = $followCursor;
        $this->boundary = $boundary;
        $this->offset = $offset;
        $this->html = $html;
        $this->animation = $animation;
        $this->animationType = $animationType;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.feedback.tooltip');
    }

    /**
     * Get base CSS classes specific to tooltip component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-tooltip-trigger'];
        
        // Theme
        $classes[] = "ds-tooltip-trigger--{$this->theme}";
        
        // Trigger type
        $classes[] = "ds-tooltip-trigger--{$this->trigger}";
        
        // Interactive
        if ($this->interactive) {
            $classes[] = 'ds-tooltip-trigger--interactive';
        }
        
        // Follow cursor
        if ($this->followCursor) {
            $classes[] = 'ds-tooltip-trigger--follow-cursor';
        }
        
        return $classes;
    }

    /**
     * Get valid positions.
     */
    public function getValidPositions(): array
    {
        return [
            'top', 'top-start', 'top-end',
            'bottom', 'bottom-start', 'bottom-end',
            'left', 'left-start', 'left-end',
            'right', 'right-start', 'right-end'
        ];
    }

    /**
     * Get valid triggers.
     */
    public function getValidTriggers(): array
    {
        return ['hover', 'click', 'focus', 'manual'];
    }

    /**
     * Get valid themes.
     */
    public function getValidThemes(): array
    {
        return ['dark', 'light', 'error', 'warning', 'success', 'info'];
    }

    /**
     * Get valid animation types.
     */
    public function getValidAnimationTypes(): array
    {
        return ['fade', 'scale', 'shift-away', 'perspective'];
    }

    /**
     * Get Alpine.js configuration for the tooltip component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'content' => $this->content,
            'position' => $this->position,
            'trigger' => $this->trigger,
            'delay' => $this->delay,
            'hideDelay' => $this->hideDelay,
            'arrow' => $this->arrow,
            'theme' => $this->theme,
            'maxWidth' => $this->maxWidth,
            'interactive' => $this->interactive,
            'followCursor' => $this->followCursor,
            'boundary' => $this->boundary,
            'offset' => $this->offset,
            'html' => $this->html,
            'animation' => $this->animation,
            'animationType' => $this->animationType,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.tooltip({$config})"
        ];
    }

    /**
     * Get unique ID for tooltip.
     */
    public function getTooltipId(): string
    {
        return 'tooltip-' . uniqid();
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
        
        $validTriggers = $this->getValidTriggers();
        if (!in_array($this->trigger, $validTriggers)) {
            throw new \InvalidArgumentException(
                "Invalid trigger '{$this->trigger}'. Valid triggers: " . implode(', ', $validTriggers)
            );
        }
        
        $validThemes = $this->getValidThemes();
        if (!in_array($this->theme, $validThemes)) {
            throw new \InvalidArgumentException(
                "Invalid theme '{$this->theme}'. Valid themes: " . implode(', ', $validThemes)
            );
        }
        
        $validAnimationTypes = $this->getValidAnimationTypes();
        if (!in_array($this->animationType, $validAnimationTypes)) {
            throw new \InvalidArgumentException(
                "Invalid animation type '{$this->animationType}'. Valid types: " . implode(', ', $validAnimationTypes)
            );
        }
        
        if ($this->delay < 0) {
            throw new \InvalidArgumentException('delay must be non-negative');
        }
        
        if ($this->hideDelay < 0) {
            throw new \InvalidArgumentException('hideDelay must be non-negative');
        }
        
        if ($this->maxWidth < 1) {
            throw new \InvalidArgumentException('maxWidth must be at least 1');
        }
        
        if ($this->offset < 0) {
            throw new \InvalidArgumentException('offset must be non-negative');
        }
    }
}