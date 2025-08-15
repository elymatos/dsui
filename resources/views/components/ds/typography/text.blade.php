{{-- Text Component with Bulma typography styling and flexible content --}}

<{{ $getTagName() }} {{ $attributes->merge($getComponentAttributes()) }}
    @if($getInlineStyles())
        style="@foreach($getInlineStyles() as $property => $value){{ $property }}: {{ $value }}; @endforeach"
    @endif>
    {{ $slot }}
</{{ $getTagName() }}>