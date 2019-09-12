<?php

namespace App\Http\Controllers;

use App\Mail\PendingEmail;
use App\Mail\ReserveConfirmation;
use App\Mail\AdminEmail;
use App\Mail\Cancellation;
use App\Reserva;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;

class ReservaController extends Controller
{
    public function __construct()
    { 
        $this->middleware('auth')->except('create');
    }

    public function index()
    {
        $reservas = Reserva::all();
        return view('adminReservas', ['reservas' => $reservas]);
    }

    public function create()
    {
        
        $reserva = new Reserva();
        return view('nuevaReserva', ['reserva' => $reserva]);
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'entry_date' => 'required',
            'out_date' => 'required',
            'message' => 'required'
        ]);

        $reserva = new Reserva();
        $reserva->name = $request->name;
        $reserva->email = $request->email;
        $reserva->message = $request->message;
        $reserva->entry_date = $request->entry_date;
        $reserva->out_date = $request->out_date;


        $reserva->save();

        // Ship_email order...
        Mail::to($reserva->email)->send(new PendingEmail($reserva));

        return redirect('nueva-reserva');
    }

    public function show(Reserva $reserva)
    {
        //
    }

    public function edit(Reserva $reserva)
    {
        return view('actualizarReserva', compact('reserva', $reserva));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $reserva->update($request->all());
        return redirect('reserva');
    }

    public function confirmReservation(Reserva $reserva)
    {

        $reserva->validate();
        Mail::to($reserva->email)->send(new ReserveConfirmation($reserva));
        return redirect('reserva');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        Mail::to($reserva->email)->send(new Cancellation($reserva));
        return redirect('reserva');
    }
}
