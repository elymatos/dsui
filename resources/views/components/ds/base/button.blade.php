@php
    $tag = $getTagName();
    $componentAttributes = $getComponentAttributes();
@endphp

<{{ $tag }} {{ $attributes->merge($componentAttributes) }}>
    @if($loading)
        <span class="ds-button__loader">
            <span class="loader is-size-7"></span>
        </span>
    @endif
    
    <span class="ds-button__content {{ $loading ? 'is-hidden' : '' }}">
        {{ $slot }}
    </span>
</{{ $tag }}>