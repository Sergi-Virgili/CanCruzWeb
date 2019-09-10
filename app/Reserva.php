<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ReservaController;

class Reserva extends Model
{
    public $confirmed;

    function validateData()
    {
        $this->request->validate( [
            'name' => 'bail|required',
            'email' => 'required|email:rfc|max:255',
            'entry_date'=> 'required|date',
            'out_date'=> 'required|date',
            'message'=> 'required'
        ]);

        return $this->request;
    }
    

    function confirmReservation()
    {
        if(!$confirmed)
        {
            return $false;
        }
        return $true;
    }
}
