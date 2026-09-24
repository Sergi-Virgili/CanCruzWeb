@extends('components.layouts.app')

@section('title', 'Dashboard')

<x-slot name="header">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/logo1.png') }}" alt="Can Cruz" class="h-10 w-auto">
            <h1 class="text-2xl font-bold">Dashboard</h1>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 border-none bg-transparent p-0 cursor-pointer">Cerrar sesión</button>
        </form>
    </div>
</x-slot>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-600 text-sm">Pendientes</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $pendingCount ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-600 text-sm">Confirmadas</div>
        <div class="text-2xl font-bold text-green-600">{{ $confirmedCount ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-600 text-sm">Entradas esta semana</div>
        <div class="text-2xl font-bold">{{ $weeklyArrivals ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-600 text-sm">Ocupación este mes</div>
        <div class="text-2xl font-bold">{{ $occupancyRate ?? 0 }}%</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">Calendario</h2>
                <a href="{{ route('admin.calendar.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Ver calendario completo →</a>
            </div>
            <p class="text-gray-500 text-sm">El calendario operativo muestra reservas confirmadas, pendientes y bloqueos administrativos.</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-4">Resumen rápido</h2>
            <ul class="space-y-2 text-sm">
                <li class="flex justify-between">
                    <span class="text-gray-600">Próximas entradas</span>
                    <span class="font-bold">{{ $weeklyArrivals ?? 0 }}</span>
                </li>
                <li class="flex justify-between">
                    <span class="text-gray-600">Bloqueadas este mes</span>
                    <span class="font-bold">{{ $blocksCount ?? 0 }}</span>
                </li>
                <li class="flex justify-between">
                    <span class="text-gray-600">Canceladas este mes</span>
                    <span class="font-bold text-red-600">{{ $cancelledCount ?? 0 }}</span>
                </li>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-bold mb-4">Reservas recientes</h2>
            @if($recentReservations->isEmpty())
                <p class="text-gray-500 text-sm">Sin reservas recientes.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach($recentReservations as $reservation)
                        <li class="border rounded p-2">
                            <div class="font-medium">{{ $reservation->name }}</div>
                            <div class="text-gray-600 text-xs">
                                {{ $reservation->entry_date->format('d/m/Y') }} - {{ $reservation->out_date->format('d/m/Y') }}
                                · <span class="capitalize">{{ $reservation->status->value }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

@if($pendingReservations->isNotEmpty())
    <div class="mt-6 bg-white rounded-lg shadow p-4">
        <h2 class="font-bold mb-4">Solicitudes pendientes</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Huésped</th>
                        <th class="px-4 py-2 text-left">Fechas</th>
                        <th class="px-4 py-2 text-left">Mensaje</th>
                        <th class="px-4 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingReservations as $reservation)
                        <tr>
                            <td class="px-4 py-2">{{ $reservation->name }}</td>
                            <td class="px-4 py-2">{{ $reservation->entry_date->format('d/m/Y') }} - {{ $reservation->out_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 max-w-md truncate">{{ $reservation->message }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.reservations.edit', $reservation) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                <form method="POST" action="{{ route('admin.reservations.confirm', $reservation) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 border-none bg-transparent p-0 cursor-pointer">Confirmar</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reservations.cancel', $reservation) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900 border-none bg-transparent p-0 cursor-pointer">Cancelar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($upcomingReservations->isNotEmpty())
    <div class="mt-6 bg-white rounded-lg shadow p-4">
        <h2 class="font-bold mb-4">Próximas confirmadas</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Huésped</th>
                        <th class="px-4 py-2 text-left">Fechas</th>
                        <th class="px-4 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($upcomingReservations as $reservation)
                        <tr>
                            <td class="px-4 py-2">{{ $reservation->name }}</td>
                            <td class="px-4 py-2">{{ $reservation->entry_date->format('d/m/Y') }} - {{ $reservation->out_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.reservations.edit', $reservation) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
