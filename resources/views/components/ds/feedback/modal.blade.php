{{-- Modal Component with focus management and accessibility --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
     id="{{ $getModalId() }}"
     x-transition.opacity.duration.300ms>
    
    {{-- Modal background overlay --}}
    <div class="modal-background ds-modal-overlay"
         x-show="open"
         x-transition.opacity.duration.200ms
         x-on:click="closeOnOverlay && !persistent ? close() : null"></div>
    
    {{-- Modal content container --}}
    <div class="modal-content ds-modal-content"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         x-trap.inert.noscroll="open"
         x-on:keydown.escape.window="closeOnEscape && !persistent ? close() : null">
        
        <div class="{{ $getDialogClasses() }}">
            {{-- Modal header --}}
            @if($title || $hasCustomHeader() || $closable)
                <header class="modal-card-head ds-modal-header">
                    {{-- Custom header content --}}
                    @if($hasCustomHeader())
                        <div class="ds-modal-header-content">
                            {!! $header !!}
                        </div>
                    @elseif($title)
                        <p class="modal-card-title ds-modal-title" id="{{ $getTitleId() }}">
                            {{ $title }}
                        </p>
                    @endif
                    
                    {{-- Close button --}}
                    @if($closable && !$persistent)
                        <button class="delete ds-modal-close" 
                                x-on:click="close()"
                                aria-label="Close modal"
                                type="button"></button>
                    @endif
                </header>
            @endif
            
            {{-- Modal body --}}
            <section class="modal-card-body ds-modal-body"
                     :class="{'ds-modal-body--scrollable': scrollable}">
                <div class="ds-modal-content-wrapper">
                    {{ $slot }}
                </div>
                
                {{-- Loading indicator --}}
                <div class="ds-modal-loading" x-show="loading">
                    <div class="ds-loading-spinner">
                        <div class="ds-spinner"></div>
                    </div>
                    <div class="ds-loading-message">Loading...</div>
                </div>
            </section>
            
            {{-- Modal footer --}}
            @if($hasCustomFooter())
                <footer class="modal-card-foot ds-modal-footer">
                    <div class="ds-modal-footer-content">
                        {!! $footer !!}
                    </div>
                </footer>
            @endif
        </div>
    </div>
    
    {{-- Focus trap sentinel (invisible elements to manage focus) --}}
    <div class="ds-modal-focus-sentinel" 
         tabindex="0" 
         x-on:focus="focusFirstElement()"
         aria-hidden="true"></div>
</div>

{{-- Modal trigger button (if specified) --}}
@if($trigger)
    <button type="button" 
            class="button ds-modal-trigger"
            x-on:click="open()"
            :aria-expanded="open"
            aria-haspopup="dialog">
        {{ $trigger }}
    </button>
@endif