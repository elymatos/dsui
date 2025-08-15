<?php

namespace App\View\Components\DS\Complex;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class FormWizard extends BaseComponent
{
    public array $steps;
    public int $currentStep;
    public bool $showProgress;
    public bool $showStepNumbers;
    public bool $allowStepNavigation;
    public bool $validateOnNext;
    public bool $saveProgress;
    public string $progressKey;
    public bool $showStepSummary;
    public string $submitButtonText;
    public string $nextButtonText;
    public string $previousButtonText;
    public string $orientation;
    public bool $animated;
    public bool $persistData;
    public array $validationRules;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        array $steps = [],
        int $currentStep = 0,
        bool $showProgress = true,
        bool $showStepNumbers = true,
        bool $allowStepNavigation = false,
        bool $validateOnNext = true,
        bool $saveProgress = false,
        string $progressKey = '',
        bool $showStepSummary = false,
        string $submitButtonText = 'Submit',
        string $nextButtonText = 'Next',
        string $previousButtonText = 'Previous',
        string $orientation = 'horizontal',
        bool $animated = true,
        bool $persistData = false,
        array $validationRules = []
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->steps = $steps;
        $this->currentStep = $currentStep;
        $this->showProgress = $showProgress;
        $this->showStepNumbers = $showStepNumbers;
        $this->allowStepNavigation = $allowStepNavigation;
        $this->validateOnNext = $validateOnNext;
        $this->saveProgress = $saveProgress;
        $this->progressKey = $progressKey ?: 'form_wizard_' . uniqid();
        $this->showStepSummary = $showStepSummary;
        $this->submitButtonText = $submitButtonText;
        $this->nextButtonText = $nextButtonText;
        $this->previousButtonText = $previousButtonText;
        $this->orientation = $orientation;
        $this->animated = $animated;
        $this->persistData = $persistData;
        $this->validationRules = $validationRules;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.complex.form-wizard');
    }

    /**
     * Get base CSS classes specific to form wizard component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-form-wizard'];
        
        // Orientation
        if ($this->orientation === 'vertical') {
            $classes[] = 'ds-form-wizard--vertical';
        }
        
        // Animation
        if ($this->animated) {
            $classes[] = 'ds-form-wizard--animated';
        }
        
        // Step navigation
        if ($this->allowStepNavigation) {
            $classes[] = 'ds-form-wizard--navigable';
        }
        
        return $classes;
    }

    /**
     * Get formatted steps data.
     */
    public function getFormattedSteps(): array
    {
        return collect($this->steps)->map(function ($step, $index) {
            return [
                'id' => $step['id'] ?? "step-{$index}",
                'title' => $step['title'] ?? "Step " . ($index + 1),
                'description' => $step['description'] ?? null,
                'icon' => $step['icon'] ?? null,
                'optional' => $step['optional'] ?? false,
                'disabled' => $step['disabled'] ?? false,
                'completed' => false,
                'valid' => true,
                'fields' => $step['fields'] ?? [],
                'template' => $step['template'] ?? null,
                'component' => $step['component'] ?? null,
                'data' => $step['data'] ?? [],
            ];
        })->toArray();
    }

    /**
     * Get valid orientations.
     */
    public function getValidOrientations(): array
    {
        return ['horizontal', 'vertical'];
    }

    /**
     * Get Alpine.js configuration for the form wizard component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'steps' => $this->getFormattedSteps(),
            'currentStep' => $this->currentStep,
            'showProgress' => $this->showProgress,
            'showStepNumbers' => $this->showStepNumbers,
            'allowStepNavigation' => $this->allowStepNavigation,
            'validateOnNext' => $this->validateOnNext,
            'saveProgress' => $this->saveProgress,
            'progressKey' => $this->progressKey,
            'showStepSummary' => $this->showStepSummary,
            'submitButtonText' => $this->submitButtonText,
            'nextButtonText' => $this->nextButtonText,
            'previousButtonText' => $this->previousButtonText,
            'orientation' => $this->orientation,
            'animated' => $this->animated,
            'persistData' => $this->persistData,
            'validationRules' => $this->validationRules,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.formWizard({$config})"
        ];
    }

    /**
     * Get unique ID for form wizard.
     */
    public function getWizardId(): string
    {
        return 'form-wizard-' . uniqid();
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validOrientations = $this->getValidOrientations();
        if (!in_array($this->orientation, $validOrientations)) {
            throw new \InvalidArgumentException(
                "Invalid orientation '{$this->orientation}'. Valid orientations: " . implode(', ', $validOrientations)
            );
        }
        
        if ($this->currentStep < 0) {
            throw new \InvalidArgumentException('currentStep must be non-negative');
        }
        
        if (empty($this->steps)) {
            throw new \InvalidArgumentException('At least one step is required');
        }
        
        if ($this->currentStep >= count($this->steps)) {
            throw new \InvalidArgumentException('currentStep cannot exceed the number of steps');
        }
    }
}