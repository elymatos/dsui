<?php

namespace App\View\Components\DS\Complex;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Tabs extends BaseComponent
{
    public array $tabs;
    public string $activeTab;
    public string $orientation;
    public bool $lazy;
    public bool $closable;
    public bool $addable;
    public string $tabsPosition;
    public bool $scrollable;
    public bool $centered;
    public string $tabStyle;
    public bool $animated;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        array $tabs = [],
        string $activeTab = '',
        string $orientation = 'horizontal',
        bool $lazy = true,
        bool $closable = false,
        bool $addable = false,
        string $tabsPosition = 'top',
        bool $scrollable = false,
        bool $centered = false,
        string $tabStyle = 'underline',
        bool $animated = true
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->tabs = $tabs;
        $this->activeTab = $activeTab ?: ($tabs[0]['id'] ?? '');
        $this->orientation = $orientation;
        $this->lazy = $lazy;
        $this->closable = $closable;
        $this->addable = $addable;
        $this->tabsPosition = $tabsPosition;
        $this->scrollable = $scrollable;
        $this->centered = $centered;
        $this->tabStyle = $tabStyle;
        $this->animated = $animated;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.complex.tabs');
    }

    /**
     * Get base CSS classes specific to tabs component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-tabs'];
        
        // Orientation
        if ($this->orientation === 'vertical') {
            $classes[] = 'ds-tabs--vertical';
        }
        
        // Tab position
        if ($this->tabsPosition !== 'top') {
            $classes[] = "ds-tabs--{$this->tabsPosition}";
        }
        
        // Scrollable tabs
        if ($this->scrollable) {
            $classes[] = 'ds-tabs--scrollable';
        }
        
        // Centered tabs
        if ($this->centered) {
            $classes[] = 'ds-tabs--centered';
        }
        
        // Tab style
        $classes[] = "ds-tabs--{$this->tabStyle}";
        
        // Animated transitions
        if ($this->animated) {
            $classes[] = 'ds-tabs--animated';
        }
        
        // Closable tabs
        if ($this->closable) {
            $classes[] = 'ds-tabs--closable';
        }
        
        // Addable tabs
        if ($this->addable) {
            $classes[] = 'ds-tabs--addable';
        }
        
        return $classes;
    }

    /**
     * Get formatted tabs data.
     */
    public function getFormattedTabs(): array
    {
        return collect($this->tabs)->map(function ($tab, $index) {
            return [
                'id' => $tab['id'] ?? "tab-{$index}",
                'label' => $tab['label'] ?? "Tab {$index}",
                'content' => $tab['content'] ?? '',
                'icon' => $tab['icon'] ?? null,
                'disabled' => $tab['disabled'] ?? false,
                'closable' => $tab['closable'] ?? $this->closable,
                'badge' => $tab['badge'] ?? null,
                'url' => $tab['url'] ?? null,
                'lazy' => $tab['lazy'] ?? $this->lazy,
                'loaded' => $tab['loaded'] ?? !$this->lazy,
            ];
        })->toArray();
    }

    /**
     * Get tab navigation classes.
     */
    public function getTabNavClasses(): string
    {
        $classes = ['ds-tabs-nav'];
        
        if ($this->scrollable) {
            $classes[] = 'ds-tabs-nav--scrollable';
        }
        
        if ($this->centered) {
            $classes[] = 'ds-tabs-nav--centered';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get tab content classes.
     */
    public function getTabContentClasses(): string
    {
        $classes = ['ds-tabs-content'];
        
        if ($this->animated) {
            $classes[] = 'ds-tabs-content--animated';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get valid orientations.
     */
    public function getValidOrientations(): array
    {
        return ['horizontal', 'vertical'];
    }

    /**
     * Get valid tab positions.
     */
    public function getValidTabsPositions(): array
    {
        return ['top', 'bottom', 'left', 'right'];
    }

    /**
     * Get valid tab styles.
     */
    public function getValidTabStyles(): array
    {
        return ['underline', 'pills', 'bordered', 'segmented'];
    }

    /**
     * Get accessibility attributes specific to tabs.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Main container doesn't need specific ARIA attributes
        // Individual tabs and panels will have their own
        
        return $attributes;
    }

    /**
     * Get unique ID for tab navigation.
     */
    public function getTabNavId(): string
    {
        return 'tabs-nav-' . uniqid();
    }

    /**
     * Get unique ID for tab content.
     */
    public function getTabContentId(): string
    {
        return 'tabs-content-' . uniqid();
    }

    /**
     * Get Alpine.js configuration for the tabs component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'tabs' => $this->getFormattedTabs(),
            'activeTab' => $this->activeTab,
            'orientation' => $this->orientation,
            'lazy' => $this->lazy,
            'closable' => $this->closable,
            'addable' => $this->addable,
            'animated' => $this->animated,
            'scrollable' => $this->scrollable,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.tabs({$config})"
        ];
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validOrientations = $this->getValidOrientations();
        if (!in_array($this->orientation, $validOrientations)) {
            throw new \InvalidArgumentException(
                "Invalid orientation '{$this->orientation}'. Valid orientations: " . implode(', ', $validOrientations)
            );
        }
        
        $validTabsPositions = $this->getValidTabsPositions();
        if (!in_array($this->tabsPosition, $validTabsPositions)) {
            throw new \InvalidArgumentException(
                "Invalid tabs position '{$this->tabsPosition}'. Valid positions: " . implode(', ', $validTabsPositions)
            );
        }
        
        $validTabStyles = $this->getValidTabStyles();
        if (!in_array($this->tabStyle, $validTabStyles)) {
            throw new \InvalidArgumentException(
                "Invalid tab style '{$this->tabStyle}'. Valid styles: " . implode(', ', $validTabStyles)
            );
        }
    }
}