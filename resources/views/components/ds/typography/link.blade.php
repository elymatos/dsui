{{-- Link Component with accessibility features and automatic external link detection --}}

<a {{ $attributes->merge(array_merge($getComponentAttributes(), $getLinkAttributes())) }}>
    {{-- Icon before text --}}
    @if($iconPosition === 'left' && $getIcon())
        <span class="ds-link-icon-wrapper ds-link-icon-wrapper--left">
            {!! $getIconHtml() !!}
        </span>
    @endif
    
    {{-- Link content --}}
    <span class="ds-link-content">
        {{ $slot }}
    </span>
    
    {{-- Icon after text --}}
    @if($iconPosition === 'right' && $getIcon())
        <span class="ds-link-icon-wrapper ds-link-icon-wrapper--right">
            {!! $getIconHtml() !!}
        </span>
    @endif
    
    {{-- Screen reader text for external links --}}
    @if($external)
        <span class="sr-only">(opens in new window)</span>
    @endif
    
    {{-- Screen reader text for download links --}}
    @if($download)
        <span class="sr-only">(downloads file)</span>
    @endif
</a>