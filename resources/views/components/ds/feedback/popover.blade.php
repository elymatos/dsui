{{-- Popover Component with rich content --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
     @mouseenter="trigger === 'hover' && showPopover($event)"
     @mouseleave="trigger === 'hover' && hidePopover($event)"
     @click="trigger === 'click' && togglePopover($event)"
     @focus="trigger === 'focus' && showPopover($event)"
     @blur="trigger === 'focus' && hidePopover($event)"
     :aria-describedby="visible ? popoverId : null">
    
    {{-- Trigger Element (slot content) --}}
    <div class="ds-popover-trigger-content">
        {{ $slot }}
    </div>
    
    {{-- Modal Backdrop --}}
    <div class="ds-popover-backdrop"
         x-show="visible && (modal || backdrop)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="modal ? hidePopover($event) : null"></div>
    
    {{-- Popover Content --}}
    <div class="ds-popover"
         x-show="visible"
         x-transition:enter="getEnterTransition()"
         x-transition:enter-start="getEnterStartClass()"
         x-transition:enter-end="getEnterEndClass()"
         x-transition:leave="getLeaveTransition()"
         x-transition:leave-start="getLeaveStartClass()"
         x-transition:leave-end="getLeaveEndClass()"
         :id="popoverId"
         role="dialog"
         :aria-modal="modal"
         :aria-labelledby="title ? popoverId + '-title' : null"
         :class="[
             `ds-popover--${theme}`,
             `ds-popover--${position}`,
             {
                 'ds-popover--interactive': interactive,
                 'ds-popover--modal': modal,
                 'ds-popover--with-arrow': arrow,
                 'ds-popover--html': html,
                 'ds-popover--closable': closable
             }
         ]"
         :style="getPopoverStyles()"
         @mouseenter="interactive && clearHideTimeout()"
         @mouseleave="interactive && hidePopover($event)"
         @click.outside="!modal && hidePopover($event)"
         @keydown.escape="hidePopover($event)">
        
        {{-- Popover Arrow --}}
        <div class="ds-popover-arrow" 
             x-show="arrow"
             :class="`ds-popover-arrow--${getArrowPosition()}`"
             :style="getArrowStyles()"></div>
        
        {{-- Popover Header --}}
        <div class="ds-popover-header" x-show="title || closable">
            <h3 class="ds-popover-title" 
                x-show="title"
                :id="popoverId + '-title'"
                x-text="title"></h3>
            
            <button type="button"
                    class="ds-popover-close"
                    x-show="closable"
                    @click="hidePopover($event)"
                    aria-label="Close popover">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
        
        {{-- Popover Body --}}
        <div class="ds-popover-body">
            {{-- HTML Content --}}
            <div x-show="html && content" x-html="content"></div>
            
            {{-- Text Content --}}
            <div x-show="!html && content" x-text="content"></div>
            
            {{-- Slot Content (when no content prop provided) --}}
            @if(empty($content))
                <div x-show="!content">
                    {{-- Custom popover content can be provided via named slots --}}
                    @isset($body)
                        {{ $body }}
                    @else
                        <p>No content provided</p>
                    @endisset
                </div>
            @endif
        </div>
        
        {{-- Popover Footer --}}
        @isset($footer)
            <div class="ds-popover-footer">
                {{ $footer }}
            </div>
        @endisset
        
        {{-- Actions --}}
        @isset($actions)
            <div class="ds-popover-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>

{{-- Focus Trap (for modal popovers) --}}
<div x-show="visible && modal" 
     x-init="modal && setupFocusTrap()"
     x-destroy="modal && destroyFocusTrap()"
     style="display: none;"></div>