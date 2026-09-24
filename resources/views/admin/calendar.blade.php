@extends('components.layouts.app')

@section('title', 'Calendario Administrativo')

<x-slot name="header">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Calendario</h1>
        <a href="{{ route('admin.reservations.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">← Tabla de reservas</a>
    </div>
</x-slot>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-center mb-4">
                <a href="?month={{ $start->copy()->subMonth()->month }}&year={{ $start->copy()->subMonth()->year }}" class="button button--compact">‹ Anterior</a>
                <span class="font-bold text-lg">{{ $start->translatedFormat('F Y') }}</span>
                <a href="?month={{ $start->copy()->addMonth()->month }}&year={{ $start->copy()->addMonth()->year }}" class="button button--compact">Siguiente ›</a>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-sm font-medium text-gray-500">
                @foreach(['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'] as $day)
                    <div>{{ $day }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1 text-sm">
                @php
                    $current = $start->copy()->startOfMonth()->copy()->day(now()->day) === now()->day && $start->copy()->month === now()->month && $start->copy()->year === now()->year;
                    $dayOfWeek = (int) $start->copy()->startOfMonth()->dayOfWeekIso;
                @endphp

                @for($i = 1; $i < $dayOfWeek; $i++)
                    <div></div>
                @endfor

                @php
                    $totalDays = (int) $start->copy()->endOfMonth()->day;
                    $dayCounter = 1;
                @endphp

                @for($i = $dayOfWeek; $i <= 42; $i++)
                    @if($dayCounter > $totalDays)
                        @break
                    @endif

                    @php
                        $date = Carbon::createFromDate($start->year, $start->month, $dayCounter);
                        $isToday = $date->isToday();
                        $isOccupied = $reservations->contains(fn ($r) => $r->entry_date->lte($date) && $r->out_date->gt($date)) || $blocks->contains(fn ($b) => $b->entry_date->lte($date) && $b->out_date->gt($date));
                        $hasConfirmed = $reservations->contains(fn ($r) => $r->status === \App\Enums\ReservationStatus::Confirmed && $r->entry_date->lte($date) && $r->out_date->gt($date));
                        $hasBlock = $blocks->first(fn ($b) => $b->entry_date->lte($date) && $b->out_date->gt($date));
                    @endphp

                    <div class="min-h-[80px] border rounded p-1 @if($isToday) ring-2 ring-clay @endif @if($isOccupied) bg-gray-50 @endif">
                        <span class="font-medium @if($isToday) text-clay @endif @if($isOccupied) text-gray-600 @endif">{{ $dayCounter }}</span>
                        @if($hasBlock)
                            <div class="text-xs text-gray-500 mt-1">Bloqueado</div>
                        @endif
                        @if($hasConfirmed)
                            <div class="text-xs text-green-600 mt-1">Confirmado</div>
                        @endif
                    </div>

                    @php
                        $dayCounter++;
                    @endphp
                @endfor
            </div>
        </div>

        <div class="mt-4 bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-2">Crear bloque de fechas</h2>
            <form method="POST" action="{{ route('admin.calendar.blocks') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Fecha de entrada</label>
                        <input type="date" name="entry_date" required min="{{ now()->toDateString() }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Fecha de salida</label>
                        <input type="date" name="out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Motivo</label>
                    <textarea name="reason" required maxlength="500" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" rows="2"></textarea>
                </div>
                <button type="submit" class="button">Bloquear fechas</button>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-4">Resumen</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Reservas pendientes</span>
                    <span class="font-bold">{{ $reservations->where('status', \App\Enums\ReservationStatus::Pending)->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Confirmadas este mes</span>
                    <span class="font-bold">{{ $reservations->where('status', \App\Enums\ReservationStatus::Confirmed)->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Bloqueadas este mes</span>
                    <span class="font-bold">{{ $blocks->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Próximas entradas</span>
                    <span class="font-bold">{{ $reservations->where('entry_date', '>=', now()->toDateString())->where('status', \App\Enums\ReservationStatus::Confirmed)->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-4">Bloques activos</h2>
            @if($blocks->isEmpty())
                <p class="text-gray-500 text-sm">Sin bloques este mes.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach($blocks as $block)
                        <li class="border rounded p-2">
                            <div class="font-medium">{{ $block->entry_date->format('d/m') }} - {{ $block->out_date->format('d/m') }}</div>
                            <div class="text-gray-600 text-xs mt-1">{{ $block->reason }}</div>
                            <form method="POST" action="{{ route('admin.calendar.blocks.destroy', $block) }}" class="mt-2 inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs" onclick="return confirm('¿Eliminar este bloque?')">Eliminar</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-4">Próximas reservas</h2>
            @if($reservations->where('status', \App\Enums\ReservationStatus::Confirmed)->where('entry_date', '>=', now()->toDateString())->isEmpty())
                <p class="text-gray-500 text-sm">Sin reservas confirmadas próximas.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach($reservations->where('status', \App\Enums\ReservationStatus::Confirmed)->where('entry_date', '>=', now()->toDateString())->sortBy('entry_date') as $reservation)
                        <li class="border rounded p-2">
                            <div class="font-medium">{{ $reservation->name }}</div>
                            <div class="text-gray-600 text-xs">{{ $reservation->entry_date->format('d/m/Y') }} - {{ $reservation->out_date->format('d/m/Y') }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
