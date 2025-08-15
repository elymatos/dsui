<?php

namespace App\View\Components\DS\Feedback;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Loading extends BaseComponent
{
    public string $type;
    public ?string $message;
    public bool $overlay;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = true,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $type = 'spinner',
        ?string $message = null,
        bool $overlay = false
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->type = $type;
        $this->message = $message;
        $this->overlay = $overlay;
    }

    public function render(): View
    {
        return view('components.ds.feedback.loading');
    }

    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-loading'];
        
        $classes[] = "ds-loading--{$this->type}";
        
        if ($this->overlay) {
            $classes[] = 'ds-loading--overlay';
        }
        
        return $classes;
    }
}