{{-- Container Component with responsive breakpoints and flexible layout options --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
    @if($getInlineStyles())
        style="@foreach($getInlineStyles() as $property => $value){{ $property }}: {{ $value }}; @endforeach"
    @endif
    @if($getCssVariables())
        @foreach($getCssVariables() as $property => $value)
            style="{{ $property }}: {{ $value }};"
        @endforeach
    @endif>
    {{ $slot }}
</div>