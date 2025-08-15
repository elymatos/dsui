@php
    $inputAttributes = $getComponentAttributes();
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
        <input {{ $attributes->merge($inputAttributes) }} />
        
        @if($iconLeft)
            <span class="icon is-small is-left">
                <i class="{{ $iconLeft }}"></i>
            </span>
        @endif
        
        @if($iconRight)
            <span class="icon is-small is-right">
                <i class="{{ $iconRight }}"></i>
            </span>
        @endif
        
        @if($loading)
            <span class="icon is-small is-right ds-input__loader">
                <span class="loader is-size-7"></span>
            </span>
        @endif
    </div>
    
    @if($hasHelp() && !$hasError())
        <p class="help" id="{{ $id }}_help">{{ $helpText }}</p>
    @endif
    
    @if($hasError())
        <p class="help is-danger" id="{{ $id }}_error">{{ $errorMessage }}</p>
    @endif
</div>