<x-layouts.app>
    <div class="max-w-7xl mx-auto">
        <nav aria-label="Navegación de administración" class="flex gap-4 mb-6">
            <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Dashboard</a>
            <a href="{{ route('admin.reservations.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Reservas</a>
            <a href="{{ route('admin.calendar.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Calendario</a>
            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 border-none bg-transparent p-0 cursor-pointer">Cerrar sesión</button>
            </form>
        </nav>

        @if ($reservations->isEmpty())
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <p class="text-gray-600">No hay reservas registradas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Huésped</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salida</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reservations as $reservation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->entry_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $reservation->out_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.reservations.edit', $reservation) }}"
                                       class="text-indigo-600 hover:text-indigo-900">Editar</a>

                                    @if ($reservation->status === App\Enums\ReservationStatus::Pending)
                                        <form method="POST" action="{{ route('admin.reservations.confirm', $reservation) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-900 border-none bg-transparent p-0 cursor-pointer"
                                                    onclick="return confirm('¿Confirmar esta reserva?');">
                                                Confirmar
                                            </button>
                                        </form>
                                    @endif

                                    @if ($reservation->status !== App\Enums\ReservationStatus::Cancelled)
                                        <form method="POST" action="{{ route('admin.reservations.cancel', $reservation) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900 border-none bg-transparent p-0 cursor-pointer"
                                                    onclick="return confirm('¿Cancelar esta reserva?');">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>