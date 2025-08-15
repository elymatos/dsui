@php
    $textareaAttributes = $getComponentAttributes();
    $fieldClasses = $getFieldClasses();
    $controlClasses = $getControlClasses();
@endphp

<div class="{{ $fieldClasses }}">
    @if($label)
        <label class="label" for="{{ $id }}" id="{{ $id }}_label">
            {{ $label }}
            @if($required)
                <span class="has-text-danger">*</span>
            @endif
        </label>
    @endif
    
    <div class="{{ $controlClasses }}">
        <textarea {{ $attributes->merge($textareaAttributes) }}>{{ $value }}</textarea>
        
        @if($loading)
            <span class="icon is-small is-right ds-textarea__loader">
                <span class="loader is-size-7"></span>
            </span>
        @endif
    </div>
    
    <div class="ds-textarea__footer">
        @if($showCounter && $maxLength)
            <div class="ds-textarea__counter">
                <span class="ds-counter-current">{{ strlen($value ?? '') }}</span>/<span class="ds-counter-max">{{ $maxLength }}</span>
            </div>
        @endif
        
        @if($hasHelp() && !$hasError())
            <p class="help" id="{{ $id }}_help">{{ $helpText }}</p>
        @endif
        
        @if($hasError())
            <p class="help is-danger" id="{{ $id }}_error">{{ $errorMessage }}</p>
        @endif
    </div>
</div>