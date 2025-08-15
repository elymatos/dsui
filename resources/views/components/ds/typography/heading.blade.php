{{-- Heading Component with Bulma typography styling and semantic HTML --}}

<{{ $getTagName() }} {{ $attributes->merge($getComponentAttributes()) }}>
    {{ $slot }}
</{{ $getTagName() }}>