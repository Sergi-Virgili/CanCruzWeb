<?php

namespace App\Http\Controllers;

use App\Models\DateBlock;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $horizon = Carbon::today()->addMonths(12);
        $today = Carbon::today();

        $occupied = Reservation::query()
            ->confirmed()
            ->where('out_date', '>', $today)
            ->where('entry_date', '<', $horizon)
            ->orderBy('entry_date')
            ->get(['entry_date', 'out_date'])
            ->map(fn (Reservation $reservation): array => [
                'entry' => $reservation->entry_date->toDateString(),
                'out' => $reservation->out_date->toDateString(),
            ])
            ->toArray();

        $blocked = DateBlock::query()
            ->where('out_date', '>', $today)
            ->where('entry_date', '<', $horizon)
            ->orderBy('entry_date')
            ->get(['entry_date', 'out_date'])
            ->map(fn (DateBlock $block): array => [
                'entry' => $block->entry_date->toDateString(),
                'out' => $block->out_date->toDateString(),
                'type' => 'blocked',
            ])
            ->toArray();

        return response()->json([
            'occupied' => array_merge($occupied, $blocked),
        ]);
    }
}
