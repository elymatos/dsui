{{-- Radio Component with Bulma styling, Alpine.js behavior, and HTMX integration --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    {{-- Radio group label --}}
    @if($label)
        <div class="ds-radio-group-label">
            {{ $label }}
        </div>
    @endif
    
    {{-- Radio group description --}}
    @if($description)
        <div id="{{ $getDescriptionId() }}" class="ds-radio-group-description">
            {{ $description }}
        </div>
    @endif
    
    {{-- Radio options container --}}
    <div class="{{ $getGroupClasses() }}" 
         role="radiogroup"
         @if($description) aria-describedby="{{ $getDescriptionId() }}" @endif>
        
        {{-- Single radio option (when not using options array) --}}
        @if(empty($options) && $slot->isNotEmpty())
            <label class="{{ $getWrapperClasses() }}" for="{{ $getRadioId($value ?? 'single') }}">
                <input type="radio"
                       id="{{ $getRadioId($value ?? 'single') }}"
                       name="{{ $name }}"
                       value="{{ $value }}"
                       class="{{ $getInputClasses() }}"
                       x-model="selectedValue"
                       x-on:change="handleChange($event.target.value)"
                       :disabled="disabled"
                       @if($isSelected()) checked @endif
                       @if($disabled) disabled @endif>
                
                {{-- Custom radio visual --}}
                <span class="ds-radio-visual"
                      :class="{
                          'is-selected': selectedValue == '{{ $value }}',
                          'is-disabled': disabled,
                          'is-{{ $variant }}': selectedValue == '{{ $value }}'
                      }">
                    <span class="ds-radio-dot"></span>
                </span>
                
                {{-- Label content --}}
                <span class="{{ $getLabelClasses() }}">
                    {{ $slot }}
                </span>
            </label>
        @endif
        
        {{-- Multiple radio options (when using options array) --}}
        @foreach($getFormattedOptions() as $option)
            <label class="{{ $getWrapperClasses() }}" 
                   for="{{ $getRadioId($option['value']) }}"
                   :class="{'is-disabled': {{ $option['disabled'] ? 'true' : 'false' }}}">
                <input type="radio"
                       id="{{ $getRadioId($option['value']) }}"
                       name="{{ $name }}"
                       value="{{ $option['value'] }}"
                       class="{{ $getInputClasses() }}"
                       x-model="selectedValue"
                       x-on:change="handleChange($event.target.value)"
                       :disabled="disabled || {{ $option['disabled'] ? 'true' : 'false' }}"
                       @if($isSelected($option['value'])) checked @endif
                       @if($disabled || $option['disabled']) disabled @endif>
                
                {{-- Custom radio visual --}}
                <span class="ds-radio-visual"
                      :class="{
                          'is-selected': selectedValue == '{{ $option['value'] }}',
                          'is-disabled': disabled || {{ $option['disabled'] ? 'true' : 'false' }},
                          'is-{{ $variant }}': selectedValue == '{{ $option['value'] }}'
                      }">
                    <span class="ds-radio-dot"></span>
                </span>
                
                {{-- Option label --}}
                <span class="{{ $getLabelClasses() }}">
                    {{ $option['label'] }}
                </span>
            </label>
            
            {{-- Option description --}}
            @if($option['description'])
                <div class="ds-radio-option-description">
                    {{ $option['description'] }}
                </div>
            @endif
        @endforeach
    </div>
    
    {{-- Loading indicator --}}
    <div class="ds-radio-loading" x-show="loading">
        <span class="icon is-small">
            <i class="fas fa-spinner fa-spin"></i>
        </span>
    </div>
</div>