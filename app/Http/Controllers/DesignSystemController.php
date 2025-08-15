<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DesignSystemController extends Controller
{
    /**
     * Show the design system documentation homepage.
     */
    public function index(): View
    {
        return view('pages.design-system.index', [
            'title' => 'DSUI Design System',
            'components' => $this->getComponentList()
        ]);
    }

    /**
     * Show documentation for a specific component.
     */
    public function component(string $component): View
    {
        $componentData = $this->getComponentData($component);
        
        if (!$componentData) {
            abort(404, "Component '{$component}' not found");
        }

        return view('pages.design-system.component', [
            'title' => $componentData['name'] . ' Component',
            'component' => $componentData
        ]);
    }

    /**
     * Show the interactive component showcase.
     */
    public function showcase(): View
    {
        return view('pages.design-system.showcase', [
            'title' => 'DSUI Component Showcase',
            'components' => $this->getComponentList()
        ]);
    }

    /**
     * API endpoint for component data.
     */
    public function componentApi(string $component): JsonResponse
    {
        $componentData = $this->getComponentData($component);
        
        if (!$componentData) {
            return response()->json(['error' => 'Component not found'], 404);
        }

        return response()->json($componentData);
    }

    /**
     * Handle HTMX API requests for button component.
     */
    public function buttonAction(Request $request, string $action): JsonResponse
    {
        switch ($action) {
            case 'click':
                return $this->handleButtonClick($request);
            case 'validate':
                return $this->validateButton($request);
            default:
                return response()->json(['error' => 'Unknown action'], 400);
        }
    }

    /**
     * Handle HTMX API requests for modal component.
     */
    public function modalAction(Request $request, string $action): JsonResponse
    {
        switch ($action) {
            case 'open':
                return response()->json([
                    'success' => true,
                    'message' => 'Modal opened',
                    'data' => ['timestamp' => now()->toISOString()]
                ]);
            case 'close':
                return response()->json([
                    'success' => true,
                    'message' => 'Modal closed',
                    'data' => ['timestamp' => now()->toISOString()]
                ]);
            default:
                return response()->json(['error' => 'Unknown action'], 400);
        }
    }

    /**
     * Handle HTMX API requests for dropdown component.
     */
    public function dropdownAction(Request $request, string $action): JsonResponse
    {
        switch ($action) {
            case 'search':
                $query = $request->input('query', '');
                $mockOptions = [
                    ['value' => '1', 'label' => 'Apple', 'category' => 'Fruits'],
                    ['value' => '2', 'label' => 'Banana', 'category' => 'Fruits'],
                    ['value' => '3', 'label' => 'Carrot', 'category' => 'Vegetables'],
                    ['value' => '4', 'label' => 'Broccoli', 'category' => 'Vegetables'],
                    ['value' => '5', 'label' => 'Orange', 'category' => 'Fruits']
                ];
                
                $filtered = array_filter($mockOptions, function($option) use ($query) {
                    return empty($query) || stripos($option['label'], $query) !== false;
                });
                
                return response()->json([
                    'success' => true,
                    'data' => array_values($filtered),
                    'query' => $query,
                    'total' => count($filtered)
                ]);
            case 'select':
                return response()->json([
                    'success' => true,
                    'message' => 'Option selected',
                    'data' => [
                        'value' => $request->input('value'),
                        'label' => $request->input('label'),
                        'timestamp' => now()->toISOString()
                    ]
                ]);
            default:
                return response()->json(['error' => 'Unknown action'], 400);
        }
    }

    /**
     * Handle HTMX API requests for data table component.
     */
    public function dataTableAction(Request $request, string $action): JsonResponse
    {
        switch ($action) {
            case 'load':
                $page = (int) $request->input('page', 1);
                $perPage = (int) $request->input('per_page', 10);
                $sortBy = $request->input('sort_by', 'id');
                $sortDir = $request->input('sort_dir', 'asc');
                
                // Mock data generation
                $mockData = collect(range(1, 100))->map(function($id) {
                    return [
                        'id' => $id,
                        'name' => 'User ' . $id,
                        'email' => 'user' . $id . '@example.com',
                        'status' => $id % 3 === 0 ? 'inactive' : 'active',
                        'created_at' => now()->subDays(rand(1, 365))->format('Y-m-d H:i:s')
                    ];
                });
                
                $sorted = $mockData->sortBy($sortBy, SORT_REGULAR, $sortDir === 'desc');
                $paginated = $sorted->slice(($page - 1) * $perPage, $perPage);
                
                return response()->json([
                    'success' => true,
                    'data' => $paginated->values(),
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $mockData->count(),
                        'total_pages' => ceil($mockData->count() / $perPage)
                    ],
                    'sort' => [
                        'by' => $sortBy,
                        'direction' => $sortDir
                    ]
                ]);
            default:
                return response()->json(['error' => 'Unknown action'], 400);
        }
    }

    /**
     * Handle HTMX API requests for form wizard component.
     */
    public function formWizardAction(Request $request, string $action): JsonResponse
    {
        switch ($action) {
            case 'validate-step':
                $step = $request->input('step');
                $data = $request->input('data', []);
                
                // Mock validation logic
                $errors = [];
                if ($step === 'personal') {
                    if (empty($data['name'])) $errors['name'] = 'Name is required';
                    if (empty($data['email'])) $errors['email'] = 'Email is required';
                } elseif ($step === 'account') {
                    if (empty($data['username'])) $errors['username'] = 'Username is required';
                    if (strlen($data['password'] ?? '') < 8) $errors['password'] = 'Password must be at least 8 characters';
                }
                
                return response()->json([
                    'valid' => empty($errors),
                    'errors' => $errors,
                    'step' => $step,
                    'can_proceed' => empty($errors)
                ]);
            case 'submit':
                // Simulate form processing
                usleep(rand(500000, 1500000)); // 0.5-1.5s delay
                
                return response()->json([
                    'success' => true,
                    'message' => 'Form submitted successfully!',
                    'redirect_url' => '/design-system/showcase',
                    'data' => $request->all()
                ]);
            default:
                return response()->json(['error' => 'Unknown action'], 400);
        }
    }

    /**
     * Handle button click actions.
     */
    protected function handleButtonClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant' => 'string|nullable',
            'action' => 'string|nullable',
            'context' => 'array|nullable'
        ]);

        // Simulate processing time
        usleep(rand(100000, 500000)); // 100-500ms

        return response()->json([
            'success' => true,
            'message' => 'Button action completed successfully',
            'data' => [
                'timestamp' => now()->toISOString(),
                'variant' => $validated['variant'] ?? 'primary',
                'processed' => true
            ]
        ]);
    }

    /**
     * Validate button configuration.
     */
    protected function validateButton(Request $request): JsonResponse
    {
        $rules = [
            'variant' => 'required|string|in:primary,secondary,success,warning,danger,info,light,dark',
            'size' => 'string|in:small,normal,medium,large',
            'disabled' => 'boolean',
            'loading' => 'boolean'
        ];

        try {
            $validated = $request->validate($rules);
            
            return response()->json([
                'valid' => true,
                'data' => $validated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get list of all available components.
     */
    protected function getComponentList(): array
    {
        return [
            'base' => [
                'button' => [
                    'name' => 'Button',
                    'description' => 'Interactive button with multiple variants and states',
                    'status' => 'implemented'
                ],
                'input' => [
                    'name' => 'Input',
                    'description' => 'Form input with validation states',
                    'status' => 'implemented'
                ],
                'textarea' => [
                    'name' => 'Textarea',
                    'description' => 'Multi-line text input with auto-resize',
                    'status' => 'implemented'
                ],
                'select' => [
                    'name' => 'Select',
                    'description' => 'Dropdown select input with custom styling',
                    'status' => 'implemented'
                ],
                'checkbox' => [
                    'name' => 'Checkbox',
                    'description' => 'Checkbox input with custom styling',
                    'status' => 'implemented'
                ],
                'radio' => [
                    'name' => 'Radio',
                    'description' => 'Radio button input with custom styling',
                    'status' => 'implemented'
                ]
            ],
            'typography' => [
                'heading' => [
                    'name' => 'Heading',
                    'description' => 'Semantic headings with consistent styling',
                    'status' => 'implemented'
                ],
                'text' => [
                    'name' => 'Text',
                    'description' => 'Text component with typography variants',
                    'status' => 'implemented'
                ],
                'link' => [
                    'name' => 'Link',
                    'description' => 'Styled links with hover states',
                    'status' => 'implemented'
                ]
            ],
            'layout' => [
                'container' => [
                    'name' => 'Container',
                    'description' => 'Responsive container with breakpoint controls',
                    'status' => 'implemented'
                ],
                'grid' => [
                    'name' => 'Grid',
                    'description' => 'Enhanced Bulma grid system',
                    'status' => 'implemented'
                ],
                'card' => [
                    'name' => 'Card',
                    'description' => 'Flexible card component with header, body, and footer',
                    'status' => 'implemented'
                ]
            ],
            'feedback' => [
                'alert' => [
                    'name' => 'Alert',
                    'description' => 'Contextual feedback messages with dismiss functionality',
                    'status' => 'implemented'
                ],
                'loading' => [
                    'name' => 'Loading',
                    'description' => 'Loading indicators and skeleton screens',
                    'status' => 'implemented'
                ],
                'modal' => [
                    'name' => 'Modal',
                    'description' => 'Accessible modal dialogs with focus management',
                    'status' => 'implemented'
                ],
                'toast' => [
                    'name' => 'Toast',
                    'description' => 'Notification toast messages with queue management',
                    'status' => 'implemented'
                ],
                'tooltip' => [
                    'name' => 'Tooltip',
                    'description' => 'Contextual tooltips with advanced positioning',
                    'status' => 'implemented'
                ],
                'popover' => [
                    'name' => 'Popover',
                    'description' => 'Rich content popovers with modal capabilities',
                    'status' => 'implemented'
                ]
            ],
            'complex' => [
                'data-table' => [
                    'name' => 'Data Table',
                    'description' => 'Advanced data table with sorting, filtering, and pagination',
                    'status' => 'implemented'
                ],
                'tabs' => [
                    'name' => 'Tabs',
                    'description' => 'Tab navigation with lazy loading and keyboard support',
                    'status' => 'implemented'
                ],
                'dropdown' => [
                    'name' => 'Dropdown',
                    'description' => 'Advanced dropdown with search, multi-select, and virtualization',
                    'status' => 'implemented'
                ],
                'form-wizard' => [
                    'name' => 'Form Wizard',
                    'description' => 'Multi-step forms with validation and progress tracking',
                    'status' => 'implemented'
                ]
            ]
        ];
    }

    /**
     * Get detailed data for a specific component.
     */
    protected function getComponentData(string $component): ?array
    {
        $components = $this->getComponentList();
        
        foreach ($components as $category => $categoryComponents) {
            if (isset($categoryComponents[$component])) {
                return array_merge($categoryComponents[$component], [
                    'id' => $component,
                    'category' => $category,
                    'examples' => $this->getComponentExamples($component),
                    'props' => $this->getComponentProps($component),
                    'methods' => $this->getComponentMethods($component)
                ]);
            }
        }
        
        return null;
    }

    /**
     * Get examples for a component.
     */
    protected function getComponentExamples(string $component): array
    {
        switch ($component) {
            case 'button':
                return [
                    'basic' => [
                        'title' => 'Basic Usage',
                        'code' => '<x-ds-button>Click me</x-ds-button>'
                    ],
                    'variants' => [
                        'title' => 'Variants',
                        'code' => '<x-ds-button variant="primary">Primary</x-ds-button>
<x-ds-button variant="secondary">Secondary</x-ds-button>
<x-ds-button variant="success">Success</x-ds-button>'
                    ],
                    'states' => [
                        'title' => 'States',
                        'code' => '<x-ds-button :loading="true">Loading</x-ds-button>
<x-ds-button :disabled="true">Disabled</x-ds-button>'
                    ]
                ];
            case 'input':
                return [
                    'basic' => [
                        'title' => 'Basic Usage',
                        'code' => '<x-ds-input label="Full Name" placeholder="Enter your name" />'
                    ],
                    'types' => [
                        'title' => 'Input Types',
                        'code' => '<x-ds-input type="email" label="Email" placeholder="user@example.com" />
<x-ds-input type="password" label="Password" />
<x-ds-input type="number" label="Age" min="0" max="120" />'
                    ],
                    'validation' => [
                        'title' => 'Validation States',
                        'code' => '<x-ds-input label="Valid Input" value="Valid value" />
<x-ds-input label="Error Input" error-message="This field is required" />
<x-ds-input label="Help Text" help-text="Enter at least 8 characters" />'
                    ]
                ];
            case 'textarea':
                return [
                    'basic' => [
                        'title' => 'Basic Usage',
                        'code' => '<x-ds-textarea label="Message" placeholder="Enter your message" />'
                    ],
                    'resize' => [
                        'title' => 'Auto-resize',
                        'code' => '<x-ds-textarea label="Comments" :auto-resize="true" rows="3" />'
                    ]
                ];
            case 'modal':
                return [
                    'basic' => [
                        'title' => 'Basic Modal',
                        'code' => '<x-ds-modal title="Confirmation">
    Are you sure you want to delete this item?
</x-ds-modal>'
                    ],
                    'with-actions' => [
                        'title' => 'With Actions',
                        'code' => '<x-ds-modal title="Settings" closable="true">
    <p>Configure your application settings.</p>
    <x-slot name="footer">
        <x-ds-button variant="primary">Save</x-ds-button>
        <x-ds-button variant="secondary">Cancel</x-ds-button>
    </x-slot>
</x-ds-modal>'
                    ]
                ];
            case 'tabs':
                return [
                    'basic' => [
                        'title' => 'Basic Tabs',
                        'code' => '<x-ds-tabs :tabs="[
    [\'id\' => \'tab1\', \'title\' => \'Tab 1\', \'content\' => \'Content 1\'],
    [\'id\' => \'tab2\', \'title\' => \'Tab 2\', \'content\' => \'Content 2\']
]" />'
                    ],
                    'lazy-loading' => [
                        'title' => 'With Lazy Loading',
                        'code' => '<x-ds-tabs :tabs="$tabs" :lazy-loading="true" />'
                    ]
                ];
            case 'dropdown':
                return [
                    'basic' => [
                        'title' => 'Basic Dropdown',
                        'code' => '<x-ds-dropdown label="Select Option" :options="[
    [\'value\' => \'1\', \'label\' => \'Option 1\'],
    [\'value\' => \'2\', \'label\' => \'Option 2\']
]" />'
                    ],
                    'searchable' => [
                        'title' => 'Searchable',
                        'code' => '<x-ds-dropdown label="Search Options" :searchable="true" :options="$options" />'
                    ]
                ];
            case 'form-wizard':
                return [
                    'basic' => [
                        'title' => 'Basic Form Wizard',
                        'code' => '<x-ds-form-wizard :steps="[
    [\'id\' => \'step1\', \'title\' => \'Personal Info\'],
    [\'id\' => \'step2\', \'title\' => \'Account Details\']
]" />'
                    ]
                ];
            case 'tooltip':
                return [
                    'basic' => [
                        'title' => 'Basic Tooltip',
                        'code' => '<x-ds-tooltip content="This is a helpful tooltip">
    Hover me
</x-ds-tooltip>'
                    ]
                ];
            case 'data-table':
                return [
                    'basic' => [
                        'title' => 'Basic Data Table',
                        'code' => '<x-ds-data-table :columns="$columns" :data="$data" :sortable="true" />'
                    ]
                ];
            default:
                return [
                    'basic' => [
                        'title' => 'Basic Usage',
                        'code' => '<x-ds-' . $component . ' />'
                    ]
                ];
        }
    }

    /**
     * Get props documentation for a component.
     */
    protected function getComponentProps(string $component): array
    {
        switch ($component) {
            case 'button':
                return [
                    'variant' => [
                        'type' => 'string',
                        'default' => 'primary',
                        'options' => ['primary', 'secondary', 'success', 'warning', 'danger', 'info'],
                        'description' => 'Visual style variant'
                    ],
                    'size' => [
                        'type' => 'string',
                        'default' => 'normal',
                        'options' => ['small', 'normal', 'medium', 'large'],
                        'description' => 'Button size'
                    ],
                    'disabled' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Disable the button'
                    ],
                    'loading' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Show loading state'
                    ],
                    'href' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Render as link if URL provided'
                    ]
                ];
            case 'input':
                return [
                    'type' => [
                        'type' => 'string',
                        'default' => 'text',
                        'options' => ['text', 'email', 'password', 'number', 'tel', 'url', 'search'],
                        'description' => 'HTML input type'
                    ],
                    'label' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Input label text'
                    ],
                    'placeholder' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Placeholder text'
                    ],
                    'value' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Input value'
                    ],
                    'required' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Mark as required field'
                    ],
                    'disabled' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Disable the input'
                    ],
                    'error-message' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Error message to display'
                    ],
                    'help-text' => [
                        'type' => 'string|null',
                        'default' => null,
                        'description' => 'Help text to display'
                    ]
                ];
            default:
                return [];
        }
    }

    /**
     * Get methods documentation for a component.
     */
    protected function getComponentMethods(string $component): array
    {
        switch ($component) {
            case 'button':
                return [
                    'click()' => 'Programmatically trigger button click',
                    'focus()' => 'Focus the button element',
                    'blur()' => 'Remove focus from button'
                ];
            case 'input':
                return [
                    'focus()' => 'Focus the input element',
                    'blur()' => 'Remove focus from input',
                    'clear()' => 'Clear input value and reset state',
                    'setValue(value)' => 'Set input value programmatically',
                    'getValue()' => 'Get current input value',
                    'validate()' => 'Trigger validation and return result'
                ];
            default:
                return [];
        }
    }
}