<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ReservaController;

class Reserva extends Model
{
    protected $fillable = ['name','email','entry_date','out_date','message'];

    public function validate()
    {
        $validation = $this->update(array('confirmation' => '1'));
        
        if($validation)
        {
            return true;
        }

        return false;
    }

}
