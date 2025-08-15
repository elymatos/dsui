<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="container is-fluid">
        <!-- Breadcrumb -->
        <nav class="breadcrumb has-arrow-separator" aria-label="breadcrumbs">
            <ul>
                <li><a href="/design-system">Design System</a></li>
                <li><a href="/design-system">{{ ucfirst($component['category']) }}</a></li>
                <li class="is-active"><a href="#" aria-current="page">{{ $component['name'] }}</a></li>
            </ul>
        </nav>

        <!-- Header -->
        <section class="hero is-light">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title is-1">{{ $component['name'] }} Component</h1>
                    <h2 class="subtitle">{{ $component['description'] }}</h2>
                    
                    <div class="tags">
                        <span class="tag is-primary">{{ ucfirst($component['category']) }}</span>
                        @if($component['status'] === 'implemented')
                            <span class="tag is-success">Ready</span>
                        @else
                            <span class="tag is-warning">{{ ucfirst($component['status']) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="section">
            <div class="container">
                @if($component['status'] === 'implemented')
                    <div class="columns">
                        <!-- Content -->
                        <div class="column is-9">
                            <!-- Examples -->
                            @if(!empty($component['examples']))
                                <h2 class="title is-2">Examples</h2>
                                
                                @foreach($component['examples'] as $exampleKey => $example)
                                    <div class="mb-6">
                                        <h3 class="title is-4">{{ $example['title'] }}</h3>
                                        
                                        <div class="card">
                                            <div class="card-content">
                                                @if($exampleKey === 'basic')
                                                    <x-ds-button>Click me</x-ds-button>
                                                @elseif($exampleKey === 'variants')
                                                    <div class="field is-grouped">
                                                        <div class="control">
                                                            <x-ds-button variant="primary">Primary</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button variant="secondary">Secondary</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button variant="success">Success</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button variant="warning">Warning</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button variant="danger">Danger</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button variant="info">Info</x-ds-button>
                                                        </div>
                                                    </div>
                                                @elseif($exampleKey === 'states')
                                                    <div class="field is-grouped">
                                                        <div class="control">
                                                            <x-ds-button :loading="true">Loading</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button :disabled="true">Disabled</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button :outlined="true">Outlined</x-ds-button>
                                                        </div>
                                                        <div class="control">
                                                            <x-ds-button :rounded="true">Rounded</x-ds-button>
                                                        </div>
                                                    </div>
                                                @elseif($exampleKey === 'alpine')
                                                    <x-ds-button 
                                                        variant="primary"
                                                        :alpine-data="['onClick' => 'handleInteractiveClick']"
                                                        x-data="componentDemo()">
                                                        Interactive Button
                                                    </x-ds-button>
                                                    
                                                    <div x-data="componentDemo()" class="mt-3">
                                                        <p x-show="clickCount > 0" class="notification is-info is-light">
                                                            Button clicked <strong x-text="clickCount"></strong> times!
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <footer class="card-footer">
                                                <div class="card-footer-item">
                                                    <div class="content">
                                                        <pre><code>{{ $example['code'] }}</code></pre>
                                                    </div>
                                                </div>
                                            </footer>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Props Documentation -->
                            @if(!empty($component['props']))
                                <h2 class="title is-2">Props</h2>
                                
                                <div class="table-container">
                                    <table class="table is-fullwidth is-striped">
                                        <thead>
                                            <tr>
                                                <th>Prop</th>
                                                <th>Type</th>
                                                <th>Default</th>
                                                <th>Options</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($component['props'] as $propName => $prop)
                                                <tr>
                                                    <td><code>{{ $propName }}</code></td>
                                                    <td><span class="tag is-light">{{ $prop['type'] }}</span></td>
                                                    <td>
                                                        @if($prop['default'] !== null)
                                                            <code>{{ is_bool($prop['default']) ? ($prop['default'] ? 'true' : 'false') : $prop['default'] }}</code>
                                                        @else
                                                            <span class="has-text-grey">null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($prop['options']))
                                                            @foreach($prop['options'] as $option)
                                                                <span class="tag is-small">{{ $option }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="has-text-grey">—</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $prop['description'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Methods Documentation -->
                            @if(!empty($component['methods']))
                                <h2 class="title is-2">Methods</h2>
                                
                                <div class="content">
                                    @foreach($component['methods'] as $method => $description)
                                        <div class="notification is-light">
                                            <h4 class="title is-5"><code>{{ $method }}</code></h4>
                                            <p>{{ $description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Sidebar -->
                        <div class="column is-3">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">Quick Actions</p>
                                </div>
                                <div class="card-content">
                                    <div class="field">
                                        <x-ds-button 
                                            variant="primary" 
                                            size="small" 
                                            fullWidth="true"
                                            href="/design-system">
                                            ← Back to Overview
                                        </x-ds-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="notification is-warning">
                        <h3 class="title is-3">Component Under Development</h3>
                        <p>This component is currently being planned and developed. Check back soon!</p>
                        
                        <div class="mt-4">
                            <x-ds-button href="/design-system">← Back to Design System</x-ds-button>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('componentDemo', () => ({
                clickCount: 0,
                
                handleInteractiveClick() {
                    this.clickCount++;
                    Alpine.store('notifications').add({
                        type: 'success',
                        title: 'Interactive Success!',
                        message: `Button clicked ${this.clickCount} times using Alpine.js`,
                        duration: 2000
                    });
                }
            }));
        });
    </script>
</body>
</html>