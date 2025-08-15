{{-- Select Component with Bulma styling, Alpine.js behavior, and HTMX integration --}}

<div class="field">
    @if($label)
        <label class="label">{{ $label }}</label>
    @endif
    
    <div class="control">
        <div {{ $attributes->merge($getComponentAttributes()) }}>
            {{-- Hidden input for form submission --}}
            @if($name)
                <input type="hidden" name="{{ $name }}" x-model="getFormValue()" />
            @endif
            
            {{-- Select trigger/display --}}
            <div class="select-trigger" 
                 x-on:click="toggle()" 
                 tabindex="0"
                 style="
                    position: relative;
                    display: flex;
                    align-items: center;
                    width: 100%;
                    min-height: 2.5em;
                    padding: 0.5em 2.5em 0.5em 0.75em;
                    background-color: white;
                    border: 1px solid #dbdbdb;
                    border-radius: 4px;
                    cursor: pointer;
                    box-sizing: border-box;
                 ">
                
                <span class="select-value" style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <span x-text="getDisplayText ? getDisplayText() : '{{ $placeholder }}'">{{ $placeholder }}</span>
                </span>
                
                {{-- Dropdown arrow --}}
                <span class="select-arrow" style="
                    position: absolute;
                    right: 0.75em;
                    top: 50%;
                    transform: translateY(-50%);
                    pointer-events: none;
                    color: #4a4a4a;
                ">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </div>
    
    {{-- Dropdown panel --}}
            <div class="select-dropdown" 
                 x-show="isOpen" 
                 x-cloak
                 style="
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    z-index: 1000;
                    margin-top: 0.25em;
                    background-color: white;
                    border: 1px solid #dbdbdb;
                    border-radius: 4px;
                    box-shadow: 0 0.5em 1em -0.125em rgba(10, 10, 10, 0.1);
                    max-height: 200px;
                    overflow-y: auto;
                 ">
                
                {{-- Options list --}}
                <div class="select-options">
                    @foreach($getFormattedOptions() as $option)
                        <div class="select-option"
                             x-on:click="selectOption({{ json_encode($option) }})"
                             style="
                                display: flex;
                                align-items: center;
                                padding: 0.5em 0.75em;
                                cursor: pointer;
                             "
                             onmouseover="this.style.backgroundColor='#f5f5f5'"
                             onmouseout="this.style.backgroundColor='transparent'">
                            
                            <span class="option-label" style="flex: 1;">{{ $option['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    @if($helpText && !$errorMessage)
        <p class="help">{{ $helpText }}</p>
    @endif
    
    @if($errorMessage)
        <p class="help is-danger">{{ $errorMessage }}</p>
    @endif
</div>