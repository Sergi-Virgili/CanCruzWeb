<?php

namespace App\Http\Requests;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'entry_date' => ['required', 'date', 'after_or_equal:today'],
            'out_date' => ['required', 'date', 'after:entry_date'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'entry_date' => 'fecha de entrada',
            'out_date' => 'fecha de salida',
            'message' => 'mensaje',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El :attribute es obligatorio.',
            'email.required' => 'El :attribute es obligatorio.',
            'email.email' => 'El :attribute debe ser una dirección válida.',
            'entry_date.required' => 'La :attribute es obligatoria.',
            'entry_date.after_or_equal' => 'La :attribute debe ser hoy o una fecha futura.',
            'out_date.required' => 'La :attribute es obligatoria.',
            'out_date.after' => 'La :attribute debe ser posterior a la fecha de entrada.',
            'message.required' => 'El :attribute es obligatorio.',
            'message.max' => 'El :attribute no puede superar los 2000 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $reservation = $this->route('reservation');

                if (! $reservation instanceof Reservation
                    || $reservation->status !== ReservationStatus::Confirmed
                    || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $entry = Carbon::parse($this->input('entry_date'));
                $out = Carbon::parse($this->input('out_date'));

                if ($out->lessThanOrEqualTo($entry)) {
                    return;
                }

                $conflict = Reservation::query()
                    ->confirmed()
                    ->overlapping($entry, $out, $reservation->id)
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('entry_date', 'Esas fechas ya están ocupadas. Elige otras.');
                }
            },
        ];
    }
}
