<?php

namespace App\Http\Controllers\Admin;

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
