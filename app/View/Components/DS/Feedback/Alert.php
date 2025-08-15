<?php

namespace App\View\Components\DS\Feedback;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Alert extends BaseComponent
{
    public ?string $title;
    public bool $dismissible;
    public ?string $icon;

    public function __construct(
        string $variant = 'info',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        ?string $title = null,
        bool $dismissible = true,
        ?string $icon = null
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->title = $title;
        $this->dismissible = $dismissible;
        $this->icon = $icon;
    }

    public function render(): View
    {
        return view('components.ds.feedback.alert');
    }

    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-alert', 'notification'];
        
        if ($this->dismissible) {
            $classes[] = 'ds-alert--dismissible';
        }
        
        return $classes;
    }
}