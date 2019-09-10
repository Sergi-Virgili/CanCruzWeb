<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\ReservaController;
use App\Reserva;
use Carbon\Carbon;

class ReserveConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $reserva;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $format = 'd/m/Y';
        $date1 = $this->reserva->entry_date;
        $date2 = $this->reserva->out_date;
        $entryDate = Carbon::create($date1);
        $outDate = Carbon::create($date2);

        return $this->view('emailConfirmationReserva')
            ->subject('Masia Can Cruz Confirmation')
            ->with([
                'name' => $this->reserva->name,
                'entry_date' => $entryDate->format('l jS \\of F Y h:i:s A'),
                'out_date' => $outDate->format('l jS \\of F Y h:i:s A'),
            ]);
    }
}
