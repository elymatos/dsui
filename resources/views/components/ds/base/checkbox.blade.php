{{-- Checkbox Component with Bulma styling, Alpine.js behavior, and HTMX integration --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    <label class="{{ $getWrapperClasses() }}" for="{{ $getCheckboxId() }}">
        {{-- Checkbox input --}}
        <input type="checkbox"
               id="{{ $getCheckboxId() }}"
               name="{{ $name }}"
               value="{{ $value }}"
               class="{{ $getInputClasses() }}"
               x-model="checked"
               x-on:change="handleChange()"
               x-ref="checkbox"
               :disabled="disabled"
               :aria-checked="getAriaChecked()"
               @if($description) aria-describedby="{{ $getDescriptionId() }}" @endif
               @if($checked) checked @endif
               @if($disabled) disabled @endif>
        
        {{-- Custom checkbox visual --}}
        <span class="ds-checkbox-visual"
              :class="{
                  'is-checked': checked && !indeterminate,
                  'is-indeterminate': indeterminate,
                  'is-disabled': disabled,
                  'is-{{ $variant }}': checked || indeterminate
              }">
            {{-- Check mark icon --}}
            <span class="ds-checkbox-icon" x-show="checked && !indeterminate">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2.5-2.5a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"/>
                </svg>
            </span>
            
            {{-- Indeterminate mark icon --}}
            <span class="ds-checkbox-icon" x-show="indeterminate">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M4 8a1 1 0 011-1h6a1 1 0 110 2H5a1 1 0 01-1-1z"/>
                </svg>
            </span>
        </span>
        
        {{-- Label content --}}
        @if($label || $slot->isNotEmpty())
            <span class="{{ $getLabelClasses() }}">
                @if($label)
                    {{ $label }}
                @else
                    {{ $slot }}
                @endif
            </span>
        @endif
    </label>
    
    {{-- Description text --}}
    @if($description)
        <div id="{{ $getDescriptionId() }}" class="ds-checkbox-description">
            {{ $description }}
        </div>
    @endif
    
    {{-- Loading indicator --}}
    <div class="ds-checkbox-loading" x-show="loading">
        <span class="icon is-small">
            <i class="fas fa-spinner fa-spin"></i>
        </span>
    </div>
</div>