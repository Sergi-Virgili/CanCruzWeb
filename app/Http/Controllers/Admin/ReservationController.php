<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::query()->latest()->paginate(20);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function edit(Reservation $reservation): View
    {
        return view('admin.reservations.edit', compact('reservation'));
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        return to_route('admin.reservations.index')
            ->with('success', 'La reserva se ha actualizado correctamente.');
    }
}
