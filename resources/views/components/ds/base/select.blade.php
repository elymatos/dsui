{{-- Select Component with Bulma styling, Alpine.js behavior, and HTMX integration --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    {{-- Hidden input for form submission --}}
    @if($name)
        <input type="hidden" name="{{ $name }}" x-model="getFormValue()" />
    @endif
    
    {{-- Select trigger/display --}}
    <div class="select-trigger" 
         x-on:click="toggle()" 
         x-on:keydown.enter.prevent="toggle()"
         x-on:keydown.space.prevent="toggle()"
         x-on:keydown.escape="close()"
         x-on:keydown.arrow-down.prevent="open(); highlightNext()"
         x-on:keydown.arrow-up.prevent="open(); highlightPrevious()"
         tabindex="0"
         :aria-expanded="isOpen"
         :aria-labelledby="$id('select-label')">
        
        <span class="select-value" 
              :class="{'is-placeholder': !hasSelection()}">
            <span x-text="getDisplayText()"></span>
        </span>
        
        {{-- Clear button for clearable selects --}}
        <button type="button" 
                class="select-clear"
                x-show="clearable && hasSelection() && !disabled"
                x-on:click.stop="clear()"
                x-transition
                aria-label="Clear selection">
            <span class="icon is-small">
                <i class="fas fa-times"></i>
            </span>
        </button>
        
        {{-- Dropdown arrow --}}
        <span class="icon is-small select-arrow" :class="{'is-rotated': isOpen}">
            <i class="fas fa-chevron-down"></i>
        </span>
    </div>
    
    {{-- Dropdown panel --}}
    <div class="select-dropdown" 
         x-show="isOpen" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-on:click.outside="close()"
         x-cloak
         :style="{'max-height': maxHeight + 'px'}">
        
        {{-- Search input for searchable selects --}}
        <div class="select-search" x-show="searchable">
            <div class="control has-icons-left">
                <input type="text" 
                       class="input is-small" 
                       x-model="searchTerm"
                       x-on:keydown.enter.prevent="selectHighlighted()"
                       x-on:keydown.escape.prevent="close()"
                       x-on:keydown.arrow-down.prevent="highlightNext()"
                       x-on:keydown.arrow-up.prevent="highlightPrevious()"
                       x-ref="searchInput"
                       :placeholder="searchPlaceholder">
                <span class="icon is-left is-small">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
        
        {{-- Options list --}}
        <div class="select-options">
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <div class="select-option"
                     :class="{
                         'is-selected': isOptionSelected(option.value),
                         'is-highlighted': highlightedIndex === index,
                         'is-disabled': option.disabled
                     }"
                     x-on:click="selectOption(option)"
                     x-on:mouseenter="highlightedIndex = index"
                     :aria-selected="isOptionSelected(option.value)"
                     role="option">
                    
                    {{-- Multiple select checkbox --}}
                    <span class="option-checkbox" x-show="multiple">
                        <input type="checkbox" 
                               :checked="isOptionSelected(option.value)"
                               tabindex="-1"
                               readonly>
                    </span>
                    
                    {{-- Option label --}}
                    <span class="option-label" x-text="option.label"></span>
                    
                    {{-- Selected indicator for single select --}}
                    <span class="option-check" x-show="!multiple && isOptionSelected(option.value)">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
            </template>
            
            {{-- No options message --}}
            <div class="select-empty" x-show="filteredOptions.length === 0">
                <span x-show="searchTerm.length > 0">No options match your search</span>
                <span x-show="searchTerm.length === 0">No options available</span>
            </div>
        </div>
    </div>
    
    {{-- Loading indicator --}}
    <div class="select-loading" x-show="loading">
        <span class="icon is-small">
            <i class="fas fa-spinner fa-spin"></i>
        </span>
    </div>
</div>