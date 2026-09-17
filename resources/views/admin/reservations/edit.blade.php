<x-layouts.app>
    <div class="max-w-xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Editar Reserva</h1>
            <a href="{{ route('admin.reservations.index') }}"
               class="text-indigo-600 hover:text-indigo-900 text-sm">← Volver al listado</a>
        </div>

        <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $reservation->name) }}"
                       required
                       maxlength="255"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                              @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $reservation->email) }}"
                       required
                       maxlength="255"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                              @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="entry_date" class="block text-sm font-medium text-gray-700">Fecha de entrada</label>
                    <input type="date"
                           id="entry_date"
                           name="entry_date"
                           value="{{ old('entry_date', $reservation->entry_date->format('Y-m-d')) }}"
                           required
                           min="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                  @error('entry_date') border-red-500 @enderror">
                    @error('entry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="out_date" class="block text-sm font-medium text-gray-700">Fecha de salida</label>
                    <input type="date"
                           id="out_date"
                           name="out_date"
                           value="{{ old('out_date', $reservation->out_date->format('Y-m-d')) }}"
                           required
                           min="{{ now()->addDay()->toDateString() }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                  @error('out_date') border-red-500 @enderror">
                    @error('out_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Mensaje</label>
                <textarea id="message"
                          name="message"
                          required
                          maxlength="2000"
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                 @error('message') border-red-500 @enderror">{{ old('message', $reservation->message) }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Máximo 2000 caracteres.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Estado actual</label>
                <div class="mt-1 p-3 bg-gray-100 rounded-md">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if ($reservation->status === App\Enums\ReservationStatus::Pending)
                            bg-yellow-100 text-yellow-800
                        @elseif ($reservation->status === App\Enums\ReservationStatus::Confirmed)
                            bg-green-100 text-green-800
                        @elseif ($reservation->status === App\Enums\ReservationStatus::Cancelled)
                            bg-red-100 text-red-800
                        @endif
                    ">
                        {{ $reservation->status->value }}
                    </span>
                    <p class="mt-1 text-xs text-gray-500">El estado no se puede editar desde aquí. Use los botones de confirmar/cancelar en el listado.</p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('admin.reservations.index') }}"
                   class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>