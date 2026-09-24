<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDateBlockRequest;
use App\Models\DateBlock;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $month = request()->query('month', now()->month);
        $year = request()->query('year', now()->year);
        $start = Carbon::createFromDate($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $reservations = Reservation::query()
            ->whereBetween('entry_date', [$start, $end])
            ->orWhereBetween('out_date', [$start, $end])
            ->orWhere(function ($query) use ($start, $end) {
                $query->where('entry_date', '<=', $start)
                    ->where('out_date', '>=', $end);
            })
            ->get();

        $blocks = DateBlock::query()
            ->whereBetween('entry_date', [$start, $end])
            ->orWhereBetween('out_date', [$start, $end])
            ->orWhere(function ($query) use ($start, $end) {
                $query->where('entry_date', '<=', $start)
                    ->where('out_date', '>=', $end);
            })
            ->get();

        return view('admin.calendar', compact('start', 'end', 'reservations', 'blocks'));
    }

    public function events(): JsonResponse
    {
        $start = Carbon::parse(request()->query('start', now()->startOfMonth()));
        $end = Carbon::parse(request()->query('end', now()->addMonth()->startOfMonth()));

        $reservations = Reservation::query()
            ->where('status', '!=', ReservationStatus::Cancelled)
            ->where('entry_date', '<', $end)
            ->where('out_date', '>', $start)
            ->orderBy('entry_date')
            ->get();

        $blocks = DateBlock::query()
            ->where('entry_date', '<', $end)
            ->where('out_date', '>', $start)
            ->orderBy('entry_date')
            ->get();

        $reservationEvents = $reservations->map(function (Reservation $reservation): array {
            $extendedProps = [
                'type' => 'reservation',
                'status' => $reservation->status->value,
                'email' => $reservation->email,
                'message' => $reservation->message,
                'editUrl' => route('admin.reservations.edit', $reservation),
                'cancelUrl' => route('admin.reservations.cancel', $reservation),
            ];

            if ($reservation->status === ReservationStatus::Pending) {
                $extendedProps['confirmUrl'] = route('admin.reservations.confirm', $reservation);
            }

            return [
                'id' => 'reservation-'.$reservation->id,
                'title' => $reservation->name,
                'start' => $reservation->entry_date->toDateString(),
                'end' => $reservation->out_date->toDateString(),
                'allDay' => true,
                'className' => ['calendar-event--'.$reservation->status->value],
                'extendedProps' => $extendedProps,
            ];
        });

        $blockEvents = $blocks->map(fn (DateBlock $block): array => [
            'id' => 'block-'.$block->id,
            'title' => 'Bloqueo',
            'start' => $block->entry_date->toDateString(),
            'end' => $block->out_date->toDateString(),
            'allDay' => true,
            'className' => ['calendar-event--block'],
            'extendedProps' => [
                'type' => 'block',
                'reason' => $block->reason,
                'deleteUrl' => route('admin.calendar.blocks.destroy', $block),
            ],
        ]);

        return response()->json($reservationEvents->concat($blockEvents)->sortBy('start')->values());
    }

    public function store(StoreDateBlockRequest $request): RedirectResponse
    {
        DateBlock::create($request->validated() + ['created_by' => auth()->id()]);

        return back()->with('success', 'El bloque de fechas se ha creado correctamente.');
    }

    public function update(StoreDateBlockRequest $request, DateBlock $block): RedirectResponse
    {
        $block->update($request->validated());

        return back()->with('success', 'El bloque de fechas se ha actualizado correctamente.');
    }

    public function destroy(DateBlock $block): RedirectResponse
    {
        $block->delete();

        return back()->with('success', 'El bloque de fechas se ha eliminado.');
    }
}
