<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Force light mode override */
        body.force-light-mode {
            background-color: white !important;
            color: #363636 !important;
        }
        
        body.force-light-mode .notification {
            background-color: #f5f5f5 !important;
            color: #363636 !important;
        }
        
        body.force-light-mode .card {
            background-color: white !important;
            color: #363636 !important;
        }
        
        body.force-light-mode .hero {
            background-color: #f5f5f5 !important;
            color: #363636 !important;
        }
        
        body.force-light-mode .hero .title {
            color: #363636 !important;
        }
        
        body.force-light-mode .hero .subtitle {
            color: #4a4a4a !important;
        }
        
        body.force-light-mode .breadcrumb a {
            color: #3273dc !important;
        }
        
        /* Force select component styling */
        .ds-select {
            position: relative !important;
            display: block !important;
            width: 100% !important;
        }
        
        .ds-select .select-trigger {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            min-height: 2.5em !important;
            padding: 0.5em 2.5em 0.5em 0.75em !important;
            background-color: white !important;
            border: 1px solid #dbdbdb !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            box-sizing: border-box !important;
        }
        
        .ds-select .select-trigger:hover {
            border-color: #b5b5b5 !important;
        }
        
        .ds-select .select-arrow {
            position: absolute !important;
            right: 0.75em !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            pointer-events: none !important;
            color: #4a4a4a !important;
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 0.5rem;
            background: #3273dc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .theme-toggle:hover {
            background: #2366d1;
        }
    </style>
</head>
<body class="force-light-mode">
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
        <i class="fas fa-sun" id="theme-icon"></i>
    </button>
    
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
                                                @if($component['id'] === 'button')
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
                                                    @endif
                                                @elseif($component['id'] === 'input')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-input label="Full Name" placeholder="Enter your name" />
                                                    @elseif($exampleKey === 'types')
                                                        <div class="columns">
                                                            <div class="column">
                                                                <x-ds-input type="email" label="Email" placeholder="user@example.com" />
                                                            </div>
                                                            <div class="column">
                                                                <x-ds-input type="password" label="Password" />
                                                            </div>
                                                        </div>
                                                        <div class="columns">
                                                            <div class="column">
                                                                <x-ds-input type="number" label="Age" min="0" max="120" />
                                                            </div>
                                                            <div class="column">
                                                                <x-ds-input type="tel" label="Phone" placeholder="+1 (555) 123-4567" />
                                                            </div>
                                                        </div>
                                                    @elseif($exampleKey === 'validation')
                                                        <div class="columns">
                                                            <div class="column">
                                                                <x-ds-input label="Valid Input" value="Valid value" class="is-success" />
                                                            </div>
                                                            <div class="column">
                                                                <x-ds-input label="Error Input" error-message="This field is required" />
                                                            </div>
                                                        </div>
                                                        <div class="column">
                                                            <x-ds-input label="Help Text" help-text="Enter at least 8 characters" />
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'textarea')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-textarea label="Message" placeholder="Enter your message" />
                                                    @elseif($exampleKey === 'resize')
                                                        <x-ds-textarea label="Auto-resize Comments" :auto-resize="true" rows="3" placeholder="This textarea will auto-resize as you type..." />
                                                    @endif
                                                @elseif($component['id'] === 'select')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-select label="Country" :options="[
                                                            ['value' => 'us', 'label' => 'United States'],
                                                            ['value' => 'ca', 'label' => 'Canada'],
                                                            ['value' => 'uk', 'label' => 'United Kingdom'],
                                                            ['value' => 'fr', 'label' => 'France']
                                                        ]" placeholder="Select a country" />
                                                    @endif
                                                @elseif($component['id'] === 'checkbox')
                                                    @if($exampleKey === 'basic')
                                                        <div class="field">
                                                            <x-ds-checkbox name="terms" label="I agree to the terms and conditions" />
                                                        </div>
                                                        <div class="field">
                                                            <x-ds-checkbox name="newsletter" label="Subscribe to newsletter" :checked="true" />
                                                        </div>
                                                        <div class="field">
                                                            <x-ds-checkbox name="disabled" label="Disabled option" :disabled="true" />
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'radio')
                                                    @if($exampleKey === 'basic')
                                                        <div class="field">
                                                            <label class="label">Preferred Contact Method</label>
                                                            <div class="field">
                                                                <x-ds-radio name="contact" value="email" label="Email" :checked="true" />
                                                            </div>
                                                            <div class="field">
                                                                <x-ds-radio name="contact" value="phone" label="Phone" />
                                                            </div>
                                                            <div class="field">
                                                                <x-ds-radio name="contact" value="mail" label="Mail" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'heading')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-heading level="1">Heading Level 1</x-ds-heading>
                                                        <x-ds-heading level="2">Heading Level 2</x-ds-heading>
                                                        <x-ds-heading level="3">Heading Level 3</x-ds-heading>
                                                        <x-ds-heading level="4">Heading Level 4</x-ds-heading>
                                                        <x-ds-heading level="5">Heading Level 5</x-ds-heading>
                                                        <x-ds-heading level="6">Heading Level 6</x-ds-heading>
                                                    @endif
                                                @elseif($component['id'] === 'text')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-text>This is regular paragraph text with default styling.</x-ds-text>
                                                        <x-ds-text size="small">This is small text for captions or footnotes.</x-ds-text>
                                                        <x-ds-text size="large">This is large text for emphasis.</x-ds-text>
                                                        <x-ds-text weight="bold">This is bold text for strong emphasis.</x-ds-text>
                                                        <x-ds-text color="primary">This is primary colored text.</x-ds-text>
                                                    @endif
                                                @elseif($component['id'] === 'link')
                                                    @if($exampleKey === 'basic')
                                                        <p>
                                                            <x-ds-link href="#internal">Internal link</x-ds-link> - 
                                                            <x-ds-link href="https://example.com" external="true">External link</x-ds-link> - 
                                                            <x-ds-link href="mailto:test@example.com">Email link</x-ds-link>
                                                        </p>
                                                        <p>
                                                            <x-ds-link href="#disabled" :disabled="true">Disabled link</x-ds-link>
                                                        </p>
                                                    @endif
                                                @elseif($component['id'] === 'container')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-container>
                                                            <div class="notification is-primary">
                                                                <p>This content is inside a responsive container that adjusts to different screen sizes.</p>
                                                            </div>
                                                        </x-ds-container>
                                                    @endif
                                                @elseif($component['id'] === 'grid')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-grid>
                                                            <div class="column is-4">
                                                                <div class="notification is-primary">Column 1</div>
                                                            </div>
                                                            <div class="column is-4">
                                                                <div class="notification is-info">Column 2</div>
                                                            </div>
                                                            <div class="column is-4">
                                                                <div class="notification is-success">Column 3</div>
                                                            </div>
                                                        </x-ds-grid>
                                                    @endif
                                                @elseif($component['id'] === 'card')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-card title="Card Example">
                                                            <p>This is the card content. Cards are flexible containers for displaying related information in a structured format.</p>
                                                            
                                                            <x-slot name="footer">
                                                                <x-ds-button size="small">Action</x-ds-button>
                                                                <x-ds-button variant="secondary" size="small">Cancel</x-ds-button>
                                                            </x-slot>
                                                        </x-ds-card>
                                                    @endif
                                                @elseif($component['id'] === 'alert')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-alert type="info" title="Information">
                                                            This is an informational alert message.
                                                        </x-ds-alert>
                                                        <x-ds-alert type="success" title="Success" class="mt-3">
                                                            Operation completed successfully!
                                                        </x-ds-alert>
                                                        <x-ds-alert type="warning" title="Warning" class="mt-3">
                                                            Please review your input before proceeding.
                                                        </x-ds-alert>
                                                        <x-ds-alert type="danger" title="Error" class="mt-3">
                                                            An error occurred while processing your request.
                                                        </x-ds-alert>
                                                    @endif
                                                @elseif($component['id'] === 'loading')
                                                    @if($exampleKey === 'basic')
                                                        <div class="field is-grouped">
                                                            <div class="control">
                                                                <x-ds-loading type="spinner" />
                                                            </div>
                                                            <div class="control">
                                                                <x-ds-loading type="dots" />
                                                            </div>
                                                            <div class="control">
                                                                <x-ds-loading type="pulse" />
                                                            </div>
                                                        </div>
                                                        <div class="mt-4">
                                                            <x-ds-loading type="skeleton" height="60px" />
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'modal')
                                                    @if($exampleKey === 'basic')
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
                                                    @endif
                                                @elseif($component['id'] === 'toast')
                                                    @if($exampleKey === 'basic')
                                                        <div class="field is-grouped">
                                                            <div class="control">
                                                                <x-ds-button variant="info" @click="
                                                                    Alpine.store('notifications').add({
                                                                        type: 'info',
                                                                        title: 'Information',
                                                                        message: 'This is an info toast notification.',
                                                                        duration: 3000
                                                                    })
                                                                ">Show Info Toast</x-ds-button>
                                                            </div>
                                                            <div class="control">
                                                                <x-ds-button variant="success" @click="
                                                                    Alpine.store('notifications').add({
                                                                        type: 'success',
                                                                        title: 'Success!',
                                                                        message: 'Operation completed successfully.',
                                                                        duration: 3000
                                                                    })
                                                                ">Show Success Toast</x-ds-button>
                                                            </div>
                                                            <div class="control">
                                                                <x-ds-button variant="danger" @click="
                                                                    Alpine.store('notifications').add({
                                                                        type: 'danger',
                                                                        title: 'Error',
                                                                        message: 'Something went wrong.',
                                                                        duration: 5000
                                                                    })
                                                                ">Show Error Toast</x-ds-button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'tooltip')
                                                    @if($exampleKey === 'basic')
                                                        <div class="field is-grouped">
                                                            <div class="control">
                                                                <x-ds-tooltip content="This is a helpful tooltip">
                                                                    <x-ds-button>Hover for tooltip</x-ds-button>
                                                                </x-ds-tooltip>
                                                            </div>
                                                            <div class="control">
                                                                <x-ds-tooltip content="Tooltip on the right" position="right">
                                                                    <x-ds-button variant="info">Right tooltip</x-ds-button>
                                                                </x-ds-tooltip>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'popover')
                                                    @if($exampleKey === 'basic')
                                                        <div x-data="{ showPopover: false }">
                                                            <x-ds-button @click="showPopover = !showPopover" variant="primary">
                                                                Toggle Popover
                                                            </x-ds-button>
                                                            
                                                            <x-ds-popover x-show="showPopover" title="Popover Title">
                                                                <p>This is a popover with rich content support.</p>
                                                                <p>It can contain multiple paragraphs, lists, and other elements.</p>
                                                                
                                                                <x-slot name="footer">
                                                                    <x-ds-button @click="showPopover = false" size="small">Close</x-ds-button>
                                                                </x-slot>
                                                            </x-ds-popover>
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'tabs')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-tabs :tabs="[
                                                            ['id' => 'tab1', 'title' => 'First Tab', 'content' => '<p>Content of the first tab. This demonstrates basic tab functionality.</p>'],
                                                            ['id' => 'tab2', 'title' => 'Second Tab', 'content' => '<p>Content of the second tab with different information.</p>'],
                                                            ['id' => 'tab3', 'title' => 'Third Tab', 'content' => '<p>Third tab content showing the tab switching capability.</p>']
                                                        ]" />
                                                    @endif
                                                @elseif($component['id'] === 'data-table')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-data-table 
                                                            :columns="[
                                                                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                                                                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                                                                ['key' => 'status', 'label' => 'Status', 'sortable' => false]
                                                            ]"
                                                            :data="[
                                                                ['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'Active'],
                                                                ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'Inactive'],
                                                                ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'Active']
                                                            ]"
                                                            :sortable="true"
                                                            :filterable="true"
                                                            :paginated="true" />
                                                    @endif
                                                @elseif($component['id'] === 'dropdown')
                                                    @if($exampleKey === 'basic')
                                                        <div class="columns">
                                                            <div class="column">
                                                                <x-ds-dropdown 
                                                                    label="Select Option"
                                                                    :options="[
                                                                        ['value' => '1', 'label' => 'Option 1'],
                                                                        ['value' => '2', 'label' => 'Option 2'],
                                                                        ['value' => '3', 'label' => 'Option 3']
                                                                    ]" />
                                                            </div>
                                                            <div class="column">
                                                                <x-ds-dropdown 
                                                                    label="Searchable Dropdown"
                                                                    :searchable="true"
                                                                    :options="[
                                                                        ['value' => 'apple', 'label' => 'Apple'],
                                                                        ['value' => 'banana', 'label' => 'Banana'],
                                                                        ['value' => 'cherry', 'label' => 'Cherry'],
                                                                        ['value' => 'date', 'label' => 'Date'],
                                                                        ['value' => 'elderberry', 'label' => 'Elderberry']
                                                                    ]" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                @elseif($component['id'] === 'form-wizard')
                                                    @if($exampleKey === 'basic')
                                                        <x-ds-form-wizard 
                                                            :steps="[
                                                                ['id' => 'personal', 'title' => 'Personal Info', 'icon' => 'fas fa-user'],
                                                                ['id' => 'account', 'title' => 'Account Details', 'icon' => 'fas fa-cog'],
                                                                ['id' => 'review', 'title' => 'Review', 'icon' => 'fas fa-check']
                                                            ]">
                                                            <x-slot name="personal">
                                                                <x-ds-input name="name" label="Full Name" required />
                                                                <x-ds-input name="email" type="email" label="Email" required />
                                                            </x-slot>
                                                            
                                                            <x-slot name="account">
                                                                <x-ds-input name="username" label="Username" required />
                                                                <x-ds-input name="password" type="password" label="Password" required />
                                                            </x-slot>
                                                            
                                                            <x-slot name="review">
                                                                <div class="notification is-info">
                                                                    <p>Please review your information before submitting.</p>
                                                                </div>
                                                            </x-slot>
                                                        </x-ds-form-wizard>
                                                    @endif
                                                @else
                                                    {{-- Generic component display --}}
                                                    <div class="notification is-info is-light">
                                                        <p><strong>{{ $component['name'] }} Component</strong></p>
                                                        <p>Live interactive example coming soon. Check the code examples below.</p>
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
        
        // Theme toggle functionality
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('theme-icon');
            
            if (body.classList.contains('force-light-mode')) {
                body.classList.remove('force-light-mode');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.add('force-light-mode');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'light');
            }
        }
        
        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            const icon = document.getElementById('theme-icon');
            
            if (savedTheme === 'dark') {
                body.classList.remove('force-light-mode');
                icon.className = 'fas fa-moon';
            } else {
                body.classList.add('force-light-mode');
                icon.className = 'fas fa-sun';
            }
        });
    </script>
</body>
</html>