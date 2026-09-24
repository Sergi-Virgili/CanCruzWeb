<div id="public-flash" role="status" aria-live="polite" aria-label="Resultado de la reserva">
    @if (session('success'))
        <div class="public-alert public-alert--success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="public-alert public-alert--warning" role="alert">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="public-alert public-alert--error" role="alert">{{ session('error') }}</div>
    @endif
</div>
