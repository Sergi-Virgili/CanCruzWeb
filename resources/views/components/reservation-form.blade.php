@props([
    'variant' => 'light',
    'title' => null,
    'submitLabel' => 'Enviar solicitud',
    'progressive' => false,
])

@php
    $isDark = $variant === 'dark';
    $isPublic = $variant === 'public';

    $labelClass = $isPublic ? 'booking-label' : ($isDark ? 'block text-sm font-medium text-white/90' : 'block text-sm font-medium text-gray-700');
    $inputClass = $isPublic
        ? 'booking-input'
        : ($isDark
        ? 'mt-1 block w-full border-0 border-b border-white/60 bg-transparent text-white placeholder-white/50 focus:border-[#D5AB3B] focus:ring-0 sm:text-sm'
        : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm');
    $errorClass = $isPublic ? 'booking-error' : ($isDark ? 'mt-1 text-sm text-red-300' : 'mt-1 text-sm text-red-600');
    $hintClass = $isPublic ? 'booking-hint' : ($isDark ? 'mt-1 text-sm text-white/60' : 'mt-1 text-sm text-gray-500');
    $errorBorder = $isDark ? 'border-red-400' : 'border-red-500';
    $titleClass = $isDark ? 'text-center font-serif text-3xl text-white' : 'text-center text-2xl font-bold';
    $buttonClass = $isPublic
        ? 'button booking-submit'
        : ($isDark
        ? 'inline-flex justify-center border border-transparent bg-[#D5AB3B] px-6 py-3 text-sm font-medium text-[#1A2332] transition-colors hover:bg-[#c19a2f] focus:outline-none'
        : 'inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2');
@endphp

<form method="POST" action="{{ route('reservations.store') }}"
      data-availability-calendar
      data-availability-url="{{ route('availability') }}"
      @if ($progressive) data-progressive-booking data-start-step="{{ $errors->any() ? 'contact' : 'dates' }}" @endif
      class="{{ $isPublic ? 'booking-form' : ($isDark ? 'space-y-4' : 'mx-auto max-w-xl space-y-6') }}">
    @csrf

    @if ($title)
        <h1 class="{{ $titleClass }}">{{ $title }}</h1>
    @endif

    <div data-date-step class="{{ $isPublic ? 'booking-date-step' : '' }}">
        <div class="{{ $isPublic ? 'booking-date-grid' : 'grid grid-cols-1 gap-6 md:grid-cols-2' }}">
            <div class="{{ $isPublic ? 'booking-date-field' : '' }}">
                <label for="entry_date" class="{{ $labelClass }}">@if ($isPublic)<span>START</span>@endif Fecha de entrada</label>
            <input type="date" id="entry_date" name="entry_date" value="{{ old('entry_date') }}" required
                    min="{{ now()->toDateString() }}"
                    @error('entry_date') aria-invalid="true" aria-describedby="entry-date-error" @enderror
                    class="{{ $inputClass }} @error('entry_date') {{ $errorBorder }} @enderror">
            @error('entry_date')
                    <p id="entry-date-error" class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
            </div>

            <div class="{{ $isPublic ? 'booking-date-field' : '' }}">
                <label for="out_date" class="{{ $labelClass }}">@if ($isPublic)<span>END</span>@endif Fecha de salida</label>
            <input type="date" id="out_date" name="out_date" value="{{ old('out_date') }}" required
                    min="{{ now()->addDay()->toDateString() }}"
                    @error('out_date') aria-invalid="true" aria-describedby="out-date-error" @enderror
                    class="{{ $inputClass }} @error('out_date') {{ $errorBorder }} @enderror">
            @error('out_date')
                    <p id="out-date-error" class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
            </div>
        </div>

        @if ($isPublic)
            <div data-calendar-mount class="booking-calendar-mount"></div>
        @endif

        <div class="{{ $isPublic ? 'booking-calendar-meta' : 'space-y-2' }}" data-calendar-meta>
            <div class="booking-legend {{ $isDark ? 'text-white/70' : 'text-gray-600' }}">
                <span><i class="booking-legend__available"></i>Disponible</span>
                <span><i class="booking-legend__occupied"></i>Ocupado</span>
            </div>
            <p class="{{ $hintClass }}" data-availability-status role="status" aria-live="polite">Cargando disponibilidad...</p>
            <p class="{{ $hintClass }}" data-availability-summary aria-live="polite" aria-atomic="true">Selecciona una entrada y una salida.</p>
        </div>

        @if ($progressive)
            <button type="button" class="button booking-continue" data-booking-continue hidden disabled>Continuar reserva</button>
        @endif
    </div>

    <div data-contact-step class="{{ $isPublic ? 'booking-contact-step' : 'space-y-6' }}">
        @if ($progressive)
            <div class="booking-contact-heading">
                <p class="eyebrow text-clay">Datos de contacto</p>
                <h3>Cuéntanos quién viene</h3>
                <button type="button" data-booking-back class="text-link">← Cambiar fechas</button>
            </div>
        @endif

        <div class="booking-contact-grid">
            <div>
                <label for="name" class="{{ $labelClass }}">Nombre completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                       @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                       class="{{ $inputClass }} @error('name') {{ $errorBorder }} @enderror">
                @error('name')<p id="name-error" class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="{{ $labelClass }}">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                       class="{{ $inputClass }} @error('email') {{ $errorBorder }} @enderror">
                @error('email')<p id="email-error" class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="message" class="{{ $labelClass }}">Mensaje</label>
            <textarea id="message" name="message" required maxlength="2000" rows="4"
                      @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                      class="{{ $inputClass }} @error('message') {{ $errorBorder }} @enderror">{{ old('message') }}</textarea>
            @error('message')<p id="message-error" class="{{ $errorClass }}">{{ $message }}</p>@enderror
            <p class="{{ $hintClass }}">Máximo 2000 caracteres.</p>
        </div>

        <div class="{{ $isPublic ? '' : 'text-center' }}">
            <button type="submit" class="{{ $buttonClass }}">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
