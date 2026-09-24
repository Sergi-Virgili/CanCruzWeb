<x-layouts.app>
    <div class="admin-calendar-page">
        <div class="admin-calendar-page__main">
            <section class="admin-card admin-calendar-card" aria-labelledby="admin-calendar-heading">
                <div class="admin-calendar-card__intro">
                    <div>
                        <p class="admin-eyebrow">Disponibilidad</p>
                        <h2 id="admin-calendar-heading" class="admin-section-title">Reservas y bloqueos</h2>
                    </div>
                    <p class="admin-calendar-card__hint">Pulsa una reserva para ver sus detalles o un día vacío para bloquearlo.</p>
                </div>

                <div
                    id="admin-calendar"
                    data-calendar-events-url="{{ route('admin.calendar.events') }}"
                    aria-label="Calendario de reservas y bloqueos"
                ></div>
            </section>
        </div>

        <aside class="admin-calendar-page__aside">
            <section class="admin-card p-4">
                <h2 class="admin-section-title mb-4">Resumen</h2>
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
            </section>

            <section class="admin-card p-4">
                <h2 class="admin-section-title mb-4">Bloques activos</h2>
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
            </section>

            <section class="admin-card p-4">
                <h2 class="admin-section-title mb-4">Próximas reservas</h2>
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
            </section>
        </aside>
    </div>

    <aside class="admin-calendar-drawer" data-calendar-drawer hidden aria-hidden="true">
        <div class="admin-calendar-drawer__backdrop" data-calendar-drawer-close></div>
        <section class="admin-calendar-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="calendar-drawer-title">
            <div class="admin-calendar-drawer__header">
                <div>
                    <p class="admin-eyebrow">Calendario</p>
                    <h2 id="calendar-drawer-title" class="admin-section-title" data-calendar-drawer-title>Detalle</h2>
                </div>
                <button class="admin-calendar-drawer__close" type="button" data-calendar-drawer-close aria-label="Cerrar detalle">×</button>
            </div>

            <div class="admin-calendar-drawer__content" data-calendar-drawer-content></div>

            <form method="POST" action="{{ route('admin.calendar.blocks.store') }}" class="admin-calendar-block-form" data-calendar-block-form hidden>
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="calendar-entry-date">Fecha de entrada</label>
                        <input id="calendar-entry-date" type="date" name="entry_date" required min="{{ now()->toDateString() }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="calendar-out-date">Fecha de salida</label>
                        <input id="calendar-out-date" type="date" name="out_date" required min="{{ now()->addDay()->toDateString() }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="calendar-block-reason">Motivo</label>
                    <textarea id="calendar-block-reason" name="reason" required maxlength="500" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" rows="3"></textarea>
                </div>
                <button type="submit" class="button">Bloquear fechas</button>
            </form>
        </section>
    </aside>
</x-layouts.app>
