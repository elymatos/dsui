<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSUI Component Showcase</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body x-data="showcaseApp()">
    <!-- Navigation -->
    <nav class="navbar is-dark" role="navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="/design-system">
                <strong>DSUI Showcase</strong>
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-end">
                <a class="navbar-item" href="/design-system">Documentation</a>
            </div>
        </div>
    </nav>

    <div class="container is-fluid">
        <div class="columns is-gapless">
            <!-- Sidebar -->
            <div class="column is-2">
                <aside class="menu p-4 has-background-light" style="height: calc(100vh - 52px); overflow-y: auto;">
                    <p class="menu-label">Component Categories</p>
                    
                    @foreach($components as $category => $categoryComponents)
                        <div class="mb-4">
                            <p class="menu-label has-text-primary">{{ ucfirst($category) }}</p>
                            <ul class="menu-list">
                                @foreach($categoryComponents as $componentId => $component)
                                    <li>
                                        <a href="#" 
                                           @click="showComponent('{{ $componentId }}', '{{ $category }}')"
                                           :class="{ 'is-active': activeComponent === '{{ $componentId }}' }">
                                            {{ $component['name'] }}
                                            <span class="tag is-small is-success ml-2">{{ count($categoryComponents) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </aside>
            </div>

            <!-- Main Content -->
            <div class="column is-10">
                <div class="section" style="height: calc(100vh - 52px); overflow-y: auto;">
                    <!-- Welcome Screen -->
                    <div x-show="!activeComponent" class="has-text-centered">
                        <div class="hero is-medium">
                            <div class="hero-body">
                                <h1 class="title is-1">
                                    <span class="icon-text">
                                        <span class="icon is-large">
                                            <i class="fas fa-palette fa-2x"></i>
                                        </span>
                                        <span>DSUI Component Showcase</span>
                                    </span>
                                </h1>
                                <p class="subtitle">Interactive demonstration of all 19 design system components</p>
                                
                                <div class="stats-grid columns is-multiline">
                                    @foreach($components as $category => $categoryComponents)
                                        <div class="column is-3">
                                            <div class="card">
                                                <div class="card-content has-text-centered">
                                                    <p class="title">{{ count($categoryComponents) }}</p>
                                                    <p class="subtitle">{{ ucfirst($category) }} Components</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="notification is-info is-light">
                                    <p><strong>Getting Started:</strong> Select any component from the sidebar to see live examples, props, and code samples.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Component Display -->
                    <div x-show="activeComponent" x-cloak>
                        <!-- Component Header -->
                        <div class="level mb-5">
                            <div class="level-left">
                                <div class="level-item">
                                    <div>
                                        <h1 class="title" x-text="componentData.name + ' Component'"></h1>
                                        <p class="subtitle" x-text="componentData.description"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <div class="tags">
                                        <span class="tag is-primary" x-text="componentData.category"></span>
                                        <span class="tag is-success">Implemented</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Examples Section -->
                        <div class="card mb-5">
                            <div class="card-header">
                                <p class="card-header-title">
                                    <span class="icon">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    Live Examples
                                </p>
                            </div>
                            <div class="card-content">
                                <!-- Base Components Examples -->
                                <template x-if="activeComponent === 'button'">
                                    <div>
                                        <h4 class="title is-5">Button Variants</h4>
                                        <div class="field is-grouped mb-4">
                                            <x-ds-button variant="primary">Primary</x-ds-button>
                                            <x-ds-button variant="secondary">Secondary</x-ds-button>
                                            <x-ds-button variant="success">Success</x-ds-button>
                                            <x-ds-button variant="warning">Warning</x-ds-button>
                                            <x-ds-button variant="danger">Danger</x-ds-button>
                                            <x-ds-button variant="info">Info</x-ds-button>
                                        </div>
                                        
                                        <h4 class="title is-5">Button States</h4>
                                        <div class="field is-grouped mb-4">
                                            <x-ds-button :loading="true">Loading</x-ds-button>
                                            <x-ds-button :disabled="true">Disabled</x-ds-button>
                                            <x-ds-button :outlined="true">Outlined</x-ds-button>
                                            <x-ds-button :rounded="true">Rounded</x-ds-button>
                                        </div>
                                        
                                        <h4 class="title is-5">Button Sizes</h4>
                                        <div class="field is-grouped">
                                            <x-ds-button size="small">Small</x-ds-button>
                                            <x-ds-button size="normal">Normal</x-ds-button>
                                            <x-ds-button size="medium">Medium</x-ds-button>
                                            <x-ds-button size="large">Large</x-ds-button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="activeComponent === 'input'">
                                    <div>
                                        <h4 class="title is-5">Input Types</h4>
                                        <div class="columns">
                                            <div class="column">
                                                <x-ds-input label="Text Input" placeholder="Enter text" />
                                            </div>
                                            <div class="column">
                                                <x-ds-input type="email" label="Email" placeholder="user@example.com" />
                                            </div>
                                        </div>
                                        <div class="columns">
                                            <div class="column">
                                                <x-ds-input type="password" label="Password" />
                                            </div>
                                            <div class="column">
                                                <x-ds-input type="number" label="Age" min="0" max="120" />
                                            </div>
                                        </div>
                                        
                                        <h4 class="title is-5">Validation States</h4>
                                        <div class="columns">
                                            <div class="column">
                                                <x-ds-input label="Success State" value="Valid input" class="is-success" />
                                            </div>
                                            <div class="column">
                                                <x-ds-input label="Error State" error-message="This field is required" />
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="activeComponent === 'modal'">
                                    <div x-data="{ showModal: false }">
                                        <x-ds-button @click="showModal = true" variant="primary">Open Modal</x-ds-button>
                                        
                                        <x-ds-modal x-show="showModal" @close="showModal = false" title="Example Modal">
                                            <p>This is a modal dialog with focus management and accessibility features.</p>
                                            <p>Press <kbd>Escape</kbd> to close or click the close button.</p>
                                            
                                            <x-slot name="footer">
                                                <x-ds-button @click="showModal = false" variant="primary">Confirm</x-ds-button>
                                                <x-ds-button @click="showModal = false" variant="secondary">Cancel</x-ds-button>
                                            </x-slot>
                                        </x-ds-modal>
                                    </div>
                                </template>

                                <!-- Default message for components without specific examples -->
                                <template x-if="!['button', 'input', 'modal'].includes(activeComponent)">
                                    <div class="notification is-info is-light">
                                        <p><strong>Interactive examples coming soon!</strong></p>
                                        <p>This component is fully implemented. Check the props table and code examples below.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Props Documentation -->
                        <div class="card mb-5" x-show="Object.keys(componentData.props || {}).length > 0">
                            <div class="card-header">
                                <p class="card-header-title">
                                    <span class="icon">
                                        <i class="fas fa-cog"></i>
                                    </span>
                                    Component Props
                                </p>
                            </div>
                            <div class="card-content">
                                <div class="table-container">
                                    <table class="table is-fullwidth is-striped">
                                        <thead>
                                            <tr>
                                                <th>Prop</th>
                                                <th>Type</th>
                                                <th>Default</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="[propName, prop] in Object.entries(componentData.props || {})" :key="propName">
                                                <tr>
                                                    <td><code x-text="propName"></code></td>
                                                    <td><span class="tag is-light" x-text="prop.type"></span></td>
                                                    <td>
                                                        <code x-text="prop.default !== null ? prop.default : 'null'"></code>
                                                    </td>
                                                    <td x-text="prop.description"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Code Examples -->
                        <div class="card">
                            <div class="card-header">
                                <p class="card-header-title">
                                    <span class="icon">
                                        <i class="fas fa-code"></i>
                                    </span>
                                    Code Examples
                                </p>
                            </div>
                            <div class="card-content">
                                <template x-for="[exampleKey, example] in Object.entries(componentData.examples || {})" :key="exampleKey">
                                    <div class="mb-4">
                                        <h4 class="title is-5" x-text="example.title"></h4>
                                        <pre class="has-background-dark has-text-light p-4"><code x-text="example.code"></code></pre>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showcaseApp() {
            return {
                activeComponent: null,
                componentData: {},
                
                showComponent(componentId, category) {
                    this.activeComponent = componentId;
                    
                    // Fetch component data via HTMX or directly from PHP data
                    fetch(`/api/ds/component/${componentId}`)
                        .then(response => response.json())
                        .then(data => {
                            this.componentData = {
                                name: data.name,
                                description: data.description,
                                category: category,
                                props: data.props || {},
                                examples: data.examples || {},
                                methods: data.methods || {}
                            };
                        })
                        .catch(error => {
                            console.warn('Could not load component data:', error);
                            // Fallback to basic data
                            this.componentData = {
                                name: componentId.charAt(0).toUpperCase() + componentId.slice(1),
                                description: 'Component description',
                                category: category,
                                props: {},
                                examples: {},
                                methods: {}
                            };
                        });
                }
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        .stats-grid .card {
            transition: transform 0.2s ease;
        }
        
        .stats-grid .card:hover {
            transform: translateY(-2px);
        }
        
        .menu-list a.is-active {
            background-color: hsl(204, 86%, 53%);
            color: white;
        }
        
        pre code {
            font-family: 'Fira Code', 'Consolas', monospace;
            font-size: 0.9rem;
        }
    </style>
</body>
</html>