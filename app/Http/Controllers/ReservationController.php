<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Mail\ReservationReceived;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReservationController extends Controller
{
    public function create()
    {
        return view('reservations.create');
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = Reservation::create($request->validated());

        try {
            Mail::to($reservation->email)->send(new ReservationReceived($reservation));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->with('warning', 'La reserva se guardo, pero no pudimos enviar el correo.');
        }

        return back()
            ->with('success', 'Hemos recibido tu solicitud de reserva.');
    }
}
