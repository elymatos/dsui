<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;

class DesignSystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerComponents();
        $this->registerDirectives();
        $this->loadViewsFrom(resource_path('views/components/ds'), 'ds');
    }

    /**
     * Register all design system components.
     */
    protected function registerComponents(): void
    {
        // Base Components
        Blade::component('ds-button', \App\View\Components\DS\Base\Button::class);
        Blade::component('ds-input', \App\View\Components\DS\Base\Input::class);
        Blade::component('ds-textarea', \App\View\Components\DS\Base\Textarea::class);
        Blade::component('ds-select', \App\View\Components\DS\Base\Select::class);
        Blade::component('ds-checkbox', \App\View\Components\DS\Base\Checkbox::class);
        Blade::component('ds-radio', \App\View\Components\DS\Base\Radio::class);
        
        // Typography Components
        Blade::component('ds-heading', \App\View\Components\DS\Typography\Heading::class);
        Blade::component('ds-text', \App\View\Components\DS\Typography\Text::class);
        Blade::component('ds-link', \App\View\Components\DS\Typography\Link::class);
        
        // Layout Components
        Blade::component('ds-container', \App\View\Components\DS\Layout\Container::class);
        Blade::component('ds-grid', \App\View\Components\DS\Layout\Grid::class);
        Blade::component('ds-card', \App\View\Components\DS\Layout\Card::class);
        
        // Feedback Components
        Blade::component('ds-alert', \App\View\Components\DS\Feedback\Alert::class);
        Blade::component('ds-loading', \App\View\Components\DS\Feedback\Loading::class);
        
        // Form Components
        Blade::component('ds-form', \App\View\Components\DS\Forms\Form::class);
        Blade::component('ds-field', \App\View\Components\DS\Forms\Field::class);
        Blade::component('ds-fieldset', \App\View\Components\DS\Forms\Fieldset::class);
        
        // Feedback Components
        Blade::component('ds-modal', \App\View\Components\DS\Feedback\Modal::class);
        Blade::component('ds-alert', \App\View\Components\DS\Feedback\Alert::class);
        Blade::component('ds-notification', \App\View\Components\DS\Feedback\Notification::class);
        Blade::component('ds-loading', \App\View\Components\DS\Feedback\Loading::class);
        
        // Complex Components
        Blade::component('ds-data-table', \App\View\Components\DS\Complex\DataTable::class);
        Blade::component('ds-tabs', \App\View\Components\DS\Complex\Tabs::class);
    }

    /**
     * Register custom Blade directives for design system.
     */
    protected function registerDirectives(): void
    {
        // Design System CSS Variables directive
        Blade::directive('dsTokens', function ($expression) {
            return "<?php echo app('ds.tokens')->render({$expression}); ?>";
        });

        // Component state directive for Alpine.js integration
        Blade::directive('dsState', function ($expression) {
            $params = str_replace(['(', ')', "'", '"'], '', $expression);
            $parts = explode(',', $params);
            $component = trim($parts[0] ?? '');
            $config = trim($parts[1] ?? '{}');
            
            return "<?php echo 'x-data=\"DS.component.{$component}({$config})\"'; ?>";
        });

        // HTMX integration directive
        Blade::directive('dsHtmx', function ($expression) {
            $params = str_replace(['(', ')', "'", '"'], '', $expression);
            $parts = explode(',', $params);
            $action = trim($parts[0] ?? '');
            $method = trim($parts[1] ?? 'post');
            
            return "<?php echo 'hx-{$method}=\"/api/ds/{$action}\" hx-indicator=\".ds-loading\" hx-swap=\"outerHTML\"'; ?>";
        });
    }
}