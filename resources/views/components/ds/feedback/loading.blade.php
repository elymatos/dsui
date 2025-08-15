{{-- Loading Component with spinner and skeleton states --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    @if($type === 'spinner')
        <div class="ds-loading-spinner">
            <div class="ds-spinner"></div>
        </div>
    @elseif($type === 'skeleton')
        <div class="ds-loading-skeleton">
            <div class="ds-skeleton-line"></div>
            <div class="ds-skeleton-line"></div>
            <div class="ds-skeleton-line ds-skeleton-line--short"></div>
        </div>
    @endif
    
    @if($message)
        <div class="ds-loading-message">{{ $message }}</div>
    @endif
</div>