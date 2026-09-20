<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $horizon = Carbon::today()->addMonths(12);

        $occupied = Reservation::query()
            ->confirmed()
            ->where('out_date', '>', Carbon::today())
            ->where('entry_date', '<', $horizon)
            ->orderBy('entry_date')
            ->get(['entry_date', 'out_date'])
            ->map(fn (Reservation $reservation): array => [
                'entry' => $reservation->entry_date->toDateString(),
                'out' => $reservation->out_date->toDateString(),
            ]);

        return response()->json(['occupied' => $occupied]);
    }
}
