<?php

namespace App\Http\Requests;

use App\Models\DateBlock;
use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreDateBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date', 'after_or_equal:today'],
            'out_date' => ['required', 'date', 'after:entry_date'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $entry = Carbon::parse($this->input('entry_date'));
                $out = Carbon::parse($this->input('out_date'));

                if ($out->lessThanOrEqualTo($entry)) {
                    return;
                }

                $conflict = Reservation::query()
                    ->confirmed()
                    ->overlapping($entry, $out)
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('entry_date', 'Esas fechas ya están ocupadas por una reserva confirmada.');

                    return;
                }

                $blockConflict = DateBlock::query()
                    ->overlapping($entry, $out)
                    ->exists();

                if ($blockConflict) {
                    $validator->errors()->add('entry_date', 'Esas fechas ya están bloqueadas por el administrador.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'entry_date' => 'fecha de entrada',
            'out_date' => 'fecha de salida',
            'reason' => 'motivo',
        ];
    }

    public function messages(): array
    {
        return [
            'entry_date.required' => 'La :attribute es obligatoria.',
            'entry_date.after_or_equal' => 'La :attribute debe ser hoy o una fecha futura.',
            'out_date.required' => 'La :attribute es obligatoria.',
            'out_date.after' => 'La :attribute debe ser posterior a la fecha de entrada.',
            'reason.required' => 'El :attribute es obligatorio.',
            'reason.max' => 'El :attribute no puede superar los 500 caracteres.',
        ];
    }
}
