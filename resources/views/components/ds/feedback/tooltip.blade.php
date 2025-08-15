{{-- Tooltip Component with positioning --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
     @mouseenter="trigger === 'hover' && showTooltip($event)"
     @mouseleave="trigger === 'hover' && hideTooltip($event)"
     @click="trigger === 'click' && toggleTooltip($event)"
     @focus="trigger === 'focus' && showTooltip($event)"
     @blur="trigger === 'focus' && hideTooltip($event)"
     @mousemove="followCursor && updateCursorPosition($event)"
     :aria-describedby="visible ? tooltipId : null">
    
    {{-- Trigger Element (slot content) --}}
    <div class="ds-tooltip-trigger-content">
        {{ $slot }}
    </div>
    
    {{-- Tooltip Content --}}
    <div class="ds-tooltip"
         x-show="visible"
         x-transition:enter="getEnterTransition()"
         x-transition:enter-start="getEnterStartClass()"
         x-transition:enter-end="getEnterEndClass()"
         x-transition:leave="getLeaveTransition()"
         x-transition:leave-start="getLeaveStartClass()"
         x-transition:leave-end="getLeaveEndClass()"
         :id="tooltipId"
         role="tooltip"
         :class="[
             `ds-tooltip--${theme}`,
             `ds-tooltip--${position}`,
             {
                 'ds-tooltip--interactive': interactive,
                 'ds-tooltip--follow-cursor': followCursor,
                 'ds-tooltip--with-arrow': arrow,
                 'ds-tooltip--html': html
             }
         ]"
         :style="getTooltipStyles()"
         @mouseenter="interactive && clearHideTimeout()"
         @mouseleave="interactive && hideTooltip($event)">
        
        {{-- Tooltip Arrow --}}
        <div class="ds-tooltip-arrow" 
             x-show="arrow"
             :class="`ds-tooltip-arrow--${getArrowPosition()}`"
             :style="getArrowStyles()"></div>
        
        {{-- Tooltip Content --}}
        <div class="ds-tooltip-content">
            {{-- HTML Content --}}
            <div x-show="html" x-html="content"></div>
            
            {{-- Text Content --}}
            <div x-show="!html" x-text="content"></div>
            
            {{-- Slot Content (when no content prop provided) --}}
            @if(empty($content))
                <div x-show="!content">
                    {{ $slot->isEmpty() ? '' : '' }}
                    {{-- Custom tooltip content can be provided via named slot --}}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Global Tooltip Styles (injected once) --}}
<style x-show="!window.dsTooltipStylesInjected" x-init="window.dsTooltipStylesInjected = true">
    .ds-tooltip {
        position: absolute;
        z-index: 9999;
        pointer-events: none;
    }
    
    .ds-tooltip--interactive {
        pointer-events: auto;
    }
    
    .ds-tooltip-content {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>