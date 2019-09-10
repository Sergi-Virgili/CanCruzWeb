<?php

namespace App\Http\Controllers;

use App\Mail\ReserveConfirmation;
use App\Reserva;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;

class ReservaController extends Controller
{   
    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $this->middleware('auth');
            // if (!Auth::check())
            // {
            //     return redirect('home');
            // }
        $reservas = Reserva::all();
        return view('adminreservas', ['reservas'=>$reservas]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       
        return view('nuevaReserva');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'name'=> 'required',
            'email'=> 'required',
            'entry_date'=> 'required',
            'out_date'=> 'required',
            'message'=> 'required'
        ]);

        $reserva = new Reserva();
        $reserva->name = $request->name;
        $reserva->email = $request->email;
        $reserva->message = $request->message;
        $reserva->entry_date = $request->entry_date;
        $reserva->out_date = $request->out_date;

        $reserva->save();

        // Ship_email order...
        Mail::to($reserva->email)->send(new ReserveConfirmation($reserva));
        
        
           

        return redirect('nueva-reserva');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function show(Reserva $reserva)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function edit(Reserva $reserva)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reserva $reserva)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();

        return redirect('reserva');
    }
}
