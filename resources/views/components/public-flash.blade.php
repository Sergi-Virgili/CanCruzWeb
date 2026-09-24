@if (session('success'))
    <div class="public-alert public-alert--success" role="status">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="public-alert public-alert--warning" role="alert">{{ session('warning') }}</div>
@endif
@if (session('error'))
    <div class="public-alert public-alert--error" role="alert">{{ session('error') }}</div>
@endif
