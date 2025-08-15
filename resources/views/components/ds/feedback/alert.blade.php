{{-- Alert Component with dismissible functionality --}}

<div {{ $attributes->merge($getComponentAttributes()) }} x-data="DS.component.alert({ dismissible: {{ $dismissible ? 'true' : 'false' }} })" x-show="visible" x-transition>
    @if($dismissible)
        <button class="delete" x-on:click="dismiss()" aria-label="Close"></button>
    @endif
    
    <div class="ds-alert-content">
        @if($title)
            <strong class="ds-alert-title">{{ $title }}</strong>
        @endif
        
        <div class="ds-alert-message">
            {{ $slot }}
        </div>
    </div>
</div>