{{-- Grid Component with enhanced Bulma columns and CSS Grid support --}}

<div {{ $attributes->merge($getComponentAttributes()) }}
    @if($getCssVariables())
        @foreach($getCssVariables() as $property => $value)
            style="{{ $property }}: {{ $value }};"
        @endforeach
    @endif>
    {{ $slot }}
</div>