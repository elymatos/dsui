{{-- Card Component with header/body/footer structure --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    @if($header)
        <header class="card-header">
            <p class="card-header-title">{{ $header }}</p>
        </header>
    @endif
    
    <div class="card-content">
        <div class="content">
            {{ $slot }}
        </div>
    </div>
    
    @if($footer)
        <footer class="card-footer">
            <div class="card-footer-item">{{ $footer }}</div>
        </footer>
    @endif
</div>