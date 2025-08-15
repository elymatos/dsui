{{-- Enhanced Dropdown Component with search and multi-select --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
     role="combobox"
     :aria-expanded="open"
     :aria-haspopup="true"
     :aria-owns="dropdownId + '-listbox'">
    
    {{-- Dropdown Trigger --}}
    <div class="ds-dropdown-trigger"
         @click="toggle"
         @keydown="handleTriggerKeydown($event)"
         tabindex="0"
         :aria-label="getAriaLabel()"
         :class="{
             'ds-dropdown-trigger--open': open,
             'ds-dropdown-trigger--disabled': disabled,
             'ds-dropdown-trigger--multiple': multiple,
             'ds-dropdown-trigger--has-value': hasValue
         }">
        
        {{-- Single Value Display --}}
        <div class="ds-dropdown-value" x-show="!multiple && selectedOptions.length > 0">
            <template x-for="option in selectedOptions" :key="option.value">
                <div class="ds-dropdown-single-value">
                    {{-- Avatar --}}
                    <img class="ds-dropdown-avatar" 
                         x-show="option.avatar" 
                         :src="option.avatar" 
                         :alt="option.label">
                    
                    {{-- Icon --}}
                    <span class="ds-dropdown-icon" 
                          x-show="option.icon && !option.avatar" 
                          x-html="option.icon"></span>
                    
                    {{-- Label --}}
                    <span class="ds-dropdown-label" x-text="option.label"></span>
                </div>
            </template>
        </div>
        
        {{-- Multiple Values Display (Tags) --}}
        <div class="ds-dropdown-tags" x-show="multiple && selectedOptions.length > 0">
            <template x-for="option in selectedOptions" :key="option.value">
                <div class="ds-dropdown-tag">
                    <span class="ds-dropdown-tag-label" x-text="option.label"></span>
                    <button type="button"
                            class="ds-dropdown-tag-remove"
                            @click.stop="removeOption(option.value)"
                            :aria-label="`Remove ${option.label}`"
                            tabindex="-1">
                        <svg viewBox="0 0 16 16" fill="currentColor">
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </button>
                </div>
            </template>
            
            {{-- Tag overflow indicator --}}
            <div class="ds-dropdown-tag ds-dropdown-tag--overflow" 
                 x-show="maxTagsToShow > 0 && selectedOptions.length > maxTagsToShow">
                <span x-text="`+${selectedOptions.length - maxTagsToShow} more`"></span>
            </div>
        </div>
        
        {{-- Placeholder --}}
        <div class="ds-dropdown-placeholder" 
             x-show="selectedOptions.length === 0"
             x-text="placeholder"></div>
        
        {{-- Search Input (when searchable and open) --}}
        <input type="text"
               class="ds-dropdown-search"
               x-show="searchable && open"
               x-model="searchQuery"
               :placeholder="searchPlaceholder"
               @input="handleSearch"
               @keydown="handleSearchKeydown($event)"
               @click.stop
               x-ref="searchInput"
               role="searchbox"
               :aria-controls="dropdownId + '-listbox'"
               aria-autocomplete="list">
        
        {{-- Controls --}}
        <div class="ds-dropdown-controls">
            {{-- Clear button --}}
            <button type="button"
                    class="ds-dropdown-clear"
                    x-show="clearable && hasValue && !disabled"
                    @click.stop="clearSelection"
                    aria-label="Clear selection"
                    tabindex="-1">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
            
            {{-- Dropdown arrow --}}
            <div class="ds-dropdown-arrow" :class="{'ds-dropdown-arrow--open': open}">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 5.646a.5.5 0 0 1 .708 0L8 8.293l2.646-2.647a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
            
            {{-- Loading indicator --}}
            <div class="ds-dropdown-loading" x-show="loading || searching">
                <div class="ds-spinner"></div>
            </div>
        </div>
    </div>
    
    {{-- Dropdown Menu --}}
    <div class="ds-dropdown-menu"
         x-show="open"
         x-transition:enter="ds-dropdown-enter"
         x-transition:enter-start="ds-dropdown-enter-start"
         x-transition:enter-end="ds-dropdown-enter-end"
         x-transition:leave="ds-dropdown-leave"
         x-transition:leave-start="ds-dropdown-leave-start"
         x-transition:leave-end="ds-dropdown-leave-end"
         @click.outside="close"
         :id="dropdownId + '-listbox'"
         role="listbox"
         :aria-multiselectable="multiple"
         :class="{
             'ds-dropdown-menu--virtualized': virtualized,
             'ds-dropdown-menu--top': actualPosition === 'top',
             'ds-dropdown-menu--bottom': actualPosition === 'bottom'
         }">
        
        {{-- Create new option (when creatable) --}}
        <div class="ds-dropdown-option ds-dropdown-option--create"
             x-show="creatable && searchQuery && !hasExactMatch && canCreateNew"
             @click="createNewOption"
             role="option"
             :aria-selected="false">
            <div class="ds-dropdown-option-content">
                <span class="ds-dropdown-option-label">Create "</span>
                <strong x-text="searchQuery"></strong>
                <span class="ds-dropdown-option-label">"</span>
            </div>
        </div>
        
        {{-- Options List --}}
        <div class="ds-dropdown-options" :style="virtualized ? `height: ${virtualHeight}px` : ''">
            <template x-for="(option, index) in visibleOptions" :key="option.value">
                <div class="ds-dropdown-option"
                     :class="{
                         'ds-dropdown-option--selected': isSelected(option.value),
                         'ds-dropdown-option--focused': focusedIndex === getOptionIndex(option),
                         'ds-dropdown-option--disabled': option.disabled,
                         'ds-dropdown-option--group-header': option.isGroupHeader
                     }"
                     @click="!option.disabled && !option.isGroupHeader && selectOption(option)"
                     @mouseenter="setFocusedIndex(getOptionIndex(option))"
                     role="option"
                     :aria-selected="isSelected(option.value)"
                     :aria-disabled="option.disabled"
                     :data-value="option.value">
                    
                    {{-- Group Header --}}
                    <div class="ds-dropdown-group-header" x-show="option.isGroupHeader">
                        <span class="ds-dropdown-group-label" x-text="option.label"></span>
                    </div>
                    
                    {{-- Regular Option --}}
                    <div class="ds-dropdown-option-content" x-show="!option.isGroupHeader">
                        {{-- Selection Checkbox (multiple mode) --}}
                        <input type="checkbox"
                               class="ds-dropdown-checkbox"
                               x-show="multiple && !option.disabled"
                               :checked="isSelected(option.value)"
                               tabindex="-1"
                               readonly>
                        
                        {{-- Avatar --}}
                        <img class="ds-dropdown-avatar" 
                             x-show="option.avatar" 
                             :src="option.avatar" 
                             :alt="option.label">
                        
                        {{-- Icon --}}
                        <span class="ds-dropdown-icon" 
                              x-show="option.icon && !option.avatar" 
                              x-html="option.icon"></span>
                        
                        {{-- Content --}}
                        <div class="ds-dropdown-option-text">
                            <div class="ds-dropdown-option-label" x-html="highlightMatch(option.label)"></div>
                            <div class="ds-dropdown-option-description" 
                                 x-show="option.description" 
                                 x-text="option.description"></div>
                        </div>
                        
                        {{-- Selection indicator (single mode) --}}
                        <div class="ds-dropdown-check" x-show="!multiple && isSelected(option.value)">
                            <svg viewBox="0 0 16 16" fill="currentColor">
                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>
            
            {{-- No options message --}}
            <div class="ds-dropdown-no-options" 
                 x-show="visibleOptions.length === 0 && !searching">
                <div class="ds-no-options-message">
                    <span x-text="searchQuery ? 'No options found' : 'No options available'"></span>
                </div>
            </div>
            
            {{-- Loading message --}}
            <div class="ds-dropdown-loading-message" x-show="searching">
                <div class="ds-loading-spinner">
                    <div class="ds-spinner"></div>
                </div>
                <span>Searching...</span>
            </div>
        </div>
    </div>
    
    {{-- Hidden Input for Form Submission --}}
    <template x-if="multiple">
        <template x-for="value in selectedValues" :key="value">
            <input type="hidden" :name="name + '[]'" :value="value">
        </template>
    </template>
    <input x-show="!multiple" type="hidden" :name="name" :value="selectedValues[0] || ''">
</div>