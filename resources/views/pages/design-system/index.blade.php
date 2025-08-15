<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container is-fluid">
        <!-- Header -->
        <section class="hero is-primary">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title is-1">DSUI Design System</h1>
                    <h2 class="subtitle">Laravel + Bulma + AlpineJS + HTMX Component Library</h2>
                    
                    <div class="buttons">
                        <x-ds-button variant="light" href="/design-system/showcase">
                            <span class="icon">
                                <i class="fas fa-palette"></i>
                            </span>
                            <span>Interactive Showcase</span>
                        </x-ds-button>
                        <x-ds-button variant="primary" outlined="true" href="/design-system/component/button">
                            View Components
                        </x-ds-button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="section">
            <div class="container">
                <div class="columns">
                    <!-- Sidebar -->
                    <div class="column is-3">
                        <aside class="menu">
                            <p class="menu-label">Components</p>
                            
                            @foreach($components as $category => $categoryComponents)
                                <p class="menu-label">{{ ucfirst($category) }}</p>
                                <ul class="menu-list">
                                    @foreach($categoryComponents as $componentId => $component)
                                        <li>
                                            <a href="/design-system/component/{{ $componentId }}"
                                               class="{{ $component['status'] === 'planned' ? 'has-text-grey' : '' }}">
                                                {{ $component['name'] }}
                                                @if($component['status'] === 'planned')
                                                    <span class="tag is-small is-light ml-2">Planned</span>
                                                @elseif($component['status'] === 'implemented')
                                                    <span class="tag is-small is-success ml-2">Ready</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </aside>
                    </div>

                    <!-- Content -->
                    <div class="column is-9">
                        <div class="content">
                            <h2 class="title is-2">Three-Layer Architecture</h2>
                            
                            <div class="columns">
                                <div class="column">
                                    <div class="card">
                                        <div class="card-header">
                                            <p class="card-header-title">
                                                <span class="icon">
                                                    <i class="fas fa-layer-group"></i>
                                                </span>
                                                Structure Layer
                                            </p>
                                        </div>
                                        <div class="card-content">
                                            <p><strong>Blade Components + Bulma CSS</strong></p>
                                            <p>Semantic HTML structure with Bulma classes for consistent styling and responsive design.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="column">
                                    <div class="card">
                                        <div class="card-header">
                                            <p class="card-header-title">
                                                <span class="icon">
                                                    <i class="fas fa-bolt"></i>
                                                </span>
                                                Behavior Layer
                                            </p>
                                        </div>
                                        <div class="card-content">
                                            <p><strong>AlpineJS</strong></p>
                                            <p>Reactive client-side behavior with state management and event handling.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="column">
                                    <div class="card">
                                        <div class="card-header">
                                            <p class="card-header-title">
                                                <span class="icon">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </span>
                                                Communication Layer
                                            </p>
                                        </div>
                                        <div class="card-content">
                                            <p><strong>HTMX</strong></p>
                                            <p>Progressive enhancement for server communication without full page reloads.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h3 class="title is-3">Quick Example</h3>
                            
                            <div class="notification is-info is-light">
                                <h4 class="title is-4">Interactive Button Demo</h4>
                                
                                <div class="field is-grouped">
                                    <div class="control">
                                        <x-ds-button 
                                            variant="primary" 
                                            :alpine-data="['onClick' => 'handleDemoClick']"
                                            htmx-action="click">
                                            Click Me!
                                        </x-ds-button>
                                    </div>
                                    
                                    <div class="control">
                                        <x-ds-button variant="success" loading="true">
                                            Loading State
                                        </x-ds-button>
                                    </div>
                                    
                                    <div class="control">
                                        <x-ds-button variant="danger" disabled="true">
                                            Disabled
                                        </x-ds-button>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <pre><code>&lt;x-ds-button variant="primary" 
             :alpine-data="['onClick' => 'handleClick']"
             htmx-action="click"&gt;
    Click Me!
&lt;/x-ds-button&gt;</code></pre>
                                </div>
                            </div>

                            <h3 class="title is-3">Getting Started</h3>
                            
                            <div class="content">
                                <ol>
                                    <li><strong>Include the design system:</strong> Add the Vite assets to your layout</li>
                                    <li><strong>Use components:</strong> Import components with <code>&lt;x-ds-*&gt;</code> syntax</li>
                                    <li><strong>Add behavior:</strong> Use <code>:alpine-data</code> for interactivity</li>
                                    <li><strong>Server communication:</strong> Add <code>htmx-action</code> for AJAX</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Demo Alpine.js handlers
        document.addEventListener('alpine:init', () => {
            Alpine.data('demoHandlers', () => ({
                handleDemoClick() {
                    Alpine.store('notifications').add({
                        type: 'success',
                        title: 'Button Clicked!',
                        message: 'The DSUI button component is working perfectly.',
                        duration: 3000
                    });
                }
            }));
        });
    </script>
</body>
</html>