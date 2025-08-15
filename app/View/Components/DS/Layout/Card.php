<?php

namespace App\View\Components\DS\Layout;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Card extends BaseComponent
{
    public ?string $header;
    public ?string $footer;
    public bool $shadow;
    public bool $hoverable;
    public string $padding;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        ?string $header = null,
        ?string $footer = null,
        bool $shadow = true,
        bool $hoverable = false,
        string $padding = 'normal'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->header = $header;
        $this->footer = $footer;
        $this->shadow = $shadow;
        $this->hoverable = $hoverable;
        $this->padding = $padding;
    }

    public function render(): View
    {
        return view('components.ds.layout.card');
    }

    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-card', 'card'];
        
        if ($this->shadow) {
            $classes[] = 'has-shadow';
        }
        
        if ($this->hoverable) {
            $classes[] = 'ds-card--hoverable';
        }
        
        if ($this->padding !== 'normal') {
            $classes[] = "ds-card--padding-{$this->padding}";
        }
        
        return $classes;
    }
}