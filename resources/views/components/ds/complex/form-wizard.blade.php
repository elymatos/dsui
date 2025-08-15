{{-- Form Wizard Component for multi-step forms --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    {{-- Progress Indicator --}}
    <div class="ds-wizard-progress" x-show="showProgress">
        {{-- Step Indicators --}}
        <div class="ds-wizard-steps">
            <template x-for="(step, index) in steps" :key="step.id">
                <div class="ds-wizard-step"
                     :class="{
                         'ds-wizard-step--current': currentStep === index,
                         'ds-wizard-step--completed': step.completed,
                         'ds-wizard-step--disabled': step.disabled,
                         'ds-wizard-step--optional': step.optional,
                         'ds-wizard-step--clickable': allowStepNavigation && !step.disabled,
                         'ds-wizard-step--invalid': !step.valid
                     }"
                     @click="allowStepNavigation && !step.disabled && goToStep(index)"
                     role="button"
                     :tabindex="allowStepNavigation && !step.disabled ? '0' : '-1'"
                     :aria-current="currentStep === index ? 'step' : null"
                     :aria-disabled="step.disabled">
                    
                    {{-- Step Number/Icon --}}
                    <div class="ds-wizard-step-marker">
                        {{-- Completed Icon --}}
                        <div class="ds-wizard-step-check" x-show="step.completed">
                            <svg viewBox="0 0 16 16" fill="currentColor">
                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                            </svg>
                        </div>
                        
                        {{-- Error Icon --}}
                        <div class="ds-wizard-step-error" x-show="!step.valid && !step.completed">
                            <svg viewBox="0 0 16 16" fill="currentColor">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                            </svg>
                        </div>
                        
                        {{-- Custom Icon --}}
                        <div class="ds-wizard-step-icon" 
                             x-show="step.icon && step.valid && !step.completed" 
                             x-html="step.icon"></div>
                        
                        {{-- Step Number --}}
                        <span class="ds-wizard-step-number" 
                              x-show="!step.icon && step.valid && !step.completed && showStepNumbers"
                              x-text="index + 1"></span>
                    </div>
                    
                    {{-- Step Info --}}
                    <div class="ds-wizard-step-info">
                        <div class="ds-wizard-step-title" x-text="step.title"></div>
                        <div class="ds-wizard-step-description" 
                             x-show="step.description" 
                             x-text="step.description"></div>
                        <div class="ds-wizard-step-optional" 
                             x-show="step.optional">(Optional)</div>
                    </div>
                    
                    {{-- Connector Line --}}
                    <div class="ds-wizard-step-connector" 
                         x-show="index < steps.length - 1"></div>
                </div>
            </template>
        </div>
        
        {{-- Progress Bar --}}
        <div class="ds-wizard-progress-bar">
            <div class="ds-wizard-progress-fill" 
                 :style="`width: ${progressPercentage}%`"></div>
        </div>
    </div>
    
    {{-- Form Content --}}
    <form class="ds-wizard-form" 
          @submit.prevent="handleSubmit"
          :id="wizardId + '-form'"
          novalidate>
        
        {{-- Step Content --}}
        <div class="ds-wizard-content">
            <template x-for="(step, index) in steps" :key="step.id">
                <div class="ds-wizard-panel"
                     x-show="currentStep === index"
                     x-transition:enter="ds-wizard-panel-enter"
                     x-transition:enter-start="ds-wizard-panel-enter-start"
                     x-transition:enter-end="ds-wizard-panel-enter-end"
                     x-transition:leave="ds-wizard-panel-leave"
                     x-transition:leave-start="ds-wizard-panel-leave-start"
                     x-transition:leave-end="ds-wizard-panel-leave-end"
                     role="tabpanel"
                     :aria-labelledby="`${wizardId}-step-${index}`"
                     :id="`${wizardId}-panel-${index}`">
                    
                    {{-- Step Header --}}
                    <div class="ds-wizard-panel-header">
                        <h2 class="ds-wizard-panel-title" x-text="step.title"></h2>
                        <p class="ds-wizard-panel-description" 
                           x-show="step.description" 
                           x-text="step.description"></p>
                    </div>
                    
                    {{-- Step Fields --}}
                    <div class="ds-wizard-panel-body">
                        {{-- Dynamic content based on step configuration --}}
                        <div class="ds-wizard-fields" x-show="step.fields && step.fields.length > 0">
                            <template x-for="field in step.fields" :key="field.name">
                                <div class="ds-field">
                                    <label class="ds-label" 
                                           :for="field.name" 
                                           x-text="field.label"
                                           :class="{'ds-label--required': field.required}"></label>
                                    
                                    {{-- Text Input --}}
                                    <input x-show="field.type === 'text' || !field.type"
                                           type="text"
                                           class="ds-input"
                                           :id="field.name"
                                           :name="field.name"
                                           :placeholder="field.placeholder"
                                           :required="field.required"
                                           :disabled="field.disabled"
                                           x-model="formData[field.name]"
                                           @blur="validateField(field)"
                                           @input="clearFieldError(field.name)">
                                    
                                    {{-- Email Input --}}
                                    <input x-show="field.type === 'email'"
                                           type="email"
                                           class="ds-input"
                                           :id="field.name"
                                           :name="field.name"
                                           :placeholder="field.placeholder"
                                           :required="field.required"
                                           :disabled="field.disabled"
                                           x-model="formData[field.name]"
                                           @blur="validateField(field)"
                                           @input="clearFieldError(field.name)">
                                    
                                    {{-- Password Input --}}
                                    <input x-show="field.type === 'password'"
                                           type="password"
                                           class="ds-input"
                                           :id="field.name"
                                           :name="field.name"
                                           :placeholder="field.placeholder"
                                           :required="field.required"
                                           :disabled="field.disabled"
                                           x-model="formData[field.name]"
                                           @blur="validateField(field)"
                                           @input="clearFieldError(field.name)">
                                    
                                    {{-- Textarea --}}
                                    <textarea x-show="field.type === 'textarea'"
                                              class="ds-textarea"
                                              :id="field.name"
                                              :name="field.name"
                                              :placeholder="field.placeholder"
                                              :required="field.required"
                                              :disabled="field.disabled"
                                              :rows="field.rows || 3"
                                              x-model="formData[field.name]"
                                              @blur="validateField(field)"
                                              @input="clearFieldError(field.name)"></textarea>
                                    
                                    {{-- Select --}}
                                    <select x-show="field.type === 'select'"
                                            class="ds-select"
                                            :id="field.name"
                                            :name="field.name"
                                            :required="field.required"
                                            :disabled="field.disabled"
                                            x-model="formData[field.name]"
                                            @change="validateField(field)">
                                        <option value="" x-text="field.placeholder || 'Select an option'"></option>
                                        <template x-for="option in field.options" :key="option.value">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                    
                                    {{-- Checkbox --}}
                                    <label x-show="field.type === 'checkbox'" class="ds-checkbox-label">
                                        <input type="checkbox"
                                               class="ds-checkbox"
                                               :id="field.name"
                                               :name="field.name"
                                               :required="field.required"
                                               :disabled="field.disabled"
                                               x-model="formData[field.name]"
                                               @change="validateField(field)">
                                        <span x-text="field.checkboxLabel || field.label"></span>
                                    </label>
                                    
                                    {{-- Radio Group --}}
                                    <div x-show="field.type === 'radio'" class="ds-radio-group">
                                        <template x-for="option in field.options" :key="option.value">
                                            <label class="ds-radio-label">
                                                <input type="radio"
                                                       class="ds-radio"
                                                       :name="field.name"
                                                       :value="option.value"
                                                       :required="field.required"
                                                       :disabled="field.disabled"
                                                       x-model="formData[field.name]"
                                                       @change="validateField(field)">
                                                <span x-text="option.label"></span>
                                            </label>
                                        </template>
                                    </div>
                                    
                                    {{-- Field Help Text --}}
                                    <div class="ds-field-help" 
                                         x-show="field.help" 
                                         x-text="field.help"></div>
                                    
                                    {{-- Field Error --}}
                                    <div class="ds-field-error" 
                                         x-show="fieldErrors[field.name]" 
                                         x-text="fieldErrors[field.name]"></div>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Custom step content (slot) --}}
                        <div class="ds-wizard-custom-content" x-show="!step.fields || step.fields.length === 0">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </template>
            
            {{-- Summary Step (if enabled) --}}
            <div class="ds-wizard-summary" 
                 x-show="showStepSummary && currentStep === steps.length">
                <div class="ds-wizard-summary-header">
                    <h2 class="ds-wizard-summary-title">Review Your Information</h2>
                    <p class="ds-wizard-summary-description">Please review your information before submitting.</p>
                </div>
                
                <div class="ds-wizard-summary-content">
                    <template x-for="(step, stepIndex) in steps" :key="step.id">
                        <div class="ds-wizard-summary-step">
                            <h3 class="ds-wizard-summary-step-title" x-text="step.title"></h3>
                            <div class="ds-wizard-summary-fields">
                                <template x-for="field in step.fields" :key="field.name">
                                    <div class="ds-wizard-summary-field" x-show="formData[field.name]">
                                        <span class="ds-wizard-summary-label" x-text="field.label"></span>
                                        <span class="ds-wizard-summary-value" x-text="getFieldDisplayValue(field)"></span>
                                    </div>
                                </template>
                            </div>
                            <button type="button" 
                                    class="ds-wizard-summary-edit"
                                    @click="goToStep(stepIndex)">
                                Edit
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        {{-- Navigation --}}
        <div class="ds-wizard-navigation">
            {{-- Previous Button --}}
            <button type="button"
                    class="ds-button ds-button--secondary"
                    x-show="currentStep > 0 || (showStepSummary && currentStep === steps.length)"
                    @click="previousStep"
                    :disabled="loading"
                    x-text="previousButtonText"></button>
            
            {{-- Next Button --}}
            <button type="button"
                    class="ds-button ds-button--primary"
                    x-show="currentStep < lastStepIndex"
                    @click="nextStep"
                    :disabled="loading || (validateOnNext && !isCurrentStepValid)"
                    x-text="nextButtonText"></button>
            
            {{-- Review Button --}}
            <button type="button"
                    class="ds-button ds-button--primary"
                    x-show="showStepSummary && currentStep === lastStepIndex"
                    @click="goToSummary"
                    :disabled="loading || !allStepsValid"
                    x-text="'Review'"></button>
            
            {{-- Submit Button --}}
            <button type="submit"
                    class="ds-button ds-button--primary"
                    x-show="(showStepSummary && currentStep === steps.length) || (!showStepSummary && currentStep === lastStepIndex)"
                    :disabled="loading || !allStepsValid"
                    :class="{'ds-button--loading': loading}">
                <span x-show="!loading" x-text="submitButtonText"></span>
                <span x-show="loading">Submitting...</span>
            </button>
        </div>
        
        {{-- Hidden inputs for form data --}}
        <template x-for="(value, key) in formData" :key="key">
            <input type="hidden" :name="key" :value="value">
        </template>
    </form>
    
    {{-- Success Message --}}
    <div class="ds-wizard-success" x-show="submitted">
        <div class="ds-wizard-success-icon">✅</div>
        <h3 class="ds-wizard-success-title">Form Submitted Successfully!</h3>
        <p class="ds-wizard-success-message">Thank you for your submission.</p>
    </div>
</div>