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
                ]
            ],
            'layout' => [
                'container' => [
                    'name' => 'Container',
                    'description' => 'Responsive container with breakpoint controls',
                    'status' => 'planned'
                ],
                'grid' => [
                    'name' => 'Grid',
                    'description' => 'Enhanced Bulma grid system',
                    'status' => 'planned'
                ]
            ],
            'feedback' => [
                'modal' => [
                    'name' => 'Modal',
                    'description' => 'Accessible modal dialogs',
                    'status' => 'planned'
                ],
                'alert' => [
                    'name' => 'Alert',
                    'description' => 'Contextual feedback messages',
                    'status' => 'planned'
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
                    ],
                    'alpine' => [
                        'title' => 'With Alpine.js',
                        'code' => '<x-ds-button :alpine-data="[\'onClick\' => \'handleButtonClick\']">
    Interactive Button
</x-ds-button>'
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
                    ],
                    'icons' => [
                        'title' => 'With Icons',
                        'code' => '<x-ds-input label="Search" icon-left="fas fa-search" />
<x-ds-input label="Email" type="email" icon-right="fas fa-envelope" />'
                    ]
                ];
            default:
                return [];
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