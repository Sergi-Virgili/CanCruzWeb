@props([
    'variant' => 'light',
    'title' => null,
    'submitLabel' => 'Enviar solicitud',
])

@php
    $isDark = $variant === 'dark';

    $labelClass = $isDark ? 'block text-sm font-medium text-white/90' : 'block text-sm font-medium text-gray-700';
    $inputClass = $isDark
        ? 'mt-1 block w-full border-0 border-b border-white/60 bg-transparent text-white placeholder-white/50 focus:border-[#D5AB3B] focus:ring-0 sm:text-sm'
        : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
    $errorClass = $isDark ? 'mt-1 text-sm text-red-300' : 'mt-1 text-sm text-red-600';
    $hintClass = $isDark ? 'mt-1 text-sm text-white/60' : 'mt-1 text-sm text-gray-500';
    $errorBorder = $isDark ? 'border-red-400' : 'border-red-500';
    $titleClass = $isDark ? 'text-center font-serif text-3xl text-white' : 'text-center text-2xl font-bold';
    $buttonClass = $isDark
        ? 'inline-flex justify-center border border-transparent bg-[#D5AB3B] px-6 py-3 text-sm font-medium text-[#1A2332] transition-colors hover:bg-[#c19a2f] focus:outline-none'
        : 'inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
@endphp

<form method="POST" action="{{ route('reservations.store') }}"
      data-availability-calendar
      data-availability-url="{{ route('availability') }}"
      class="{{ $isDark ? 'space-y-4' : 'mx-auto max-w-xl space-y-6' }}">
    @csrf

    @if ($title)
        <h1 class="{{ $titleClass }}">{{ $title }}</h1>
    @endif

    <div>
        <label for="name" class="{{ $labelClass }}">Nombre completo</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
               class="{{ $inputClass }} @error('name') {{ $errorBorder }} @enderror">
        @error('name')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255"
               class="{{ $inputClass }} @error('email') {{ $errorBorder }} @enderror">
        @error('email')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="entry_date" class="{{ $labelClass }}">Fecha de entrada</label>
            <input type="date" id="entry_date" name="entry_date" value="{{ old('entry_date') }}" required
                   min="{{ now()->toDateString() }}"
                   class="{{ $inputClass }} @error('entry_date') {{ $errorBorder }} @enderror">
            @error('entry_date')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="out_date" class="{{ $labelClass }}">Fecha de salida</label>
            <input type="date" id="out_date" name="out_date" value="{{ old('out_date') }}" required
                   min="{{ now()->addDay()->toDateString() }}"
                   class="{{ $inputClass }} @error('out_date') {{ $errorBorder }} @enderror">
            @error('out_date')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p class="{{ $hintClass }}">Los días ocupados aparecen deshabilitados en el calendario.</p>

    <div>
        <label for="message" class="{{ $labelClass }}">Mensaje</label>
        <textarea id="message" name="message" required maxlength="2000" rows="4"
                  class="{{ $inputClass }} @error('message') {{ $errorBorder }} @enderror">{{ old('message') }}</textarea>
        @error('message')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
        <p class="{{ $hintClass }}">Máximo 2000 caracteres.</p>
    </div>

    <div class="text-center">
        <button type="submit" class="{{ $buttonClass }}">{{ $submitLabel }}</button>
    </div>
</form>
