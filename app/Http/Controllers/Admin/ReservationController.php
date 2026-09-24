<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\DateBlock;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::query()->latest()->paginate(20);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function dashboard(): View
    {
        $today = Carbon::today();
        $thisWeekEnd = $today->copy()->addDays(7);

        $pendingCount = Reservation::query()->where('status', 'pending')->count();
        $confirmedCount = Reservation::query()->where('status', 'confirmed')->count();
        $weeklyArrivals = Reservation::query()
            ->where('status', 'confirmed')
            ->where('entry_date', '>=', $today)
            ->where('entry_date', '<=', $thisWeekEnd)
            ->count();
        $cancelledCount = Reservation::query()
            ->where('status', 'cancelled')
            ->whereMonth('cancelled_at', $today->month)
            ->count();
        $blocksCount = DateBlock::query()
            ->whereMonth('created_at', $today->month)
            ->count();

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $occupiedDays = Reservation::query()
            ->confirmed()
            ->where('entry_date', '<=', $monthEnd)
            ->where('out_date', '>=', $monthStart)
            ->get()
            ->flatMap(fn (Reservation $r) => [
                $r->entry_date->toDateString(),
                $r->out_date->toDateString(),
            ])
            ->unique()
            ->count();
        $totalDays = (int) $today->copy()->daysInMonth;
        $occupancyRate = $totalDays > 0 ? round(($occupiedDays / $totalDays) * 100) : 0;

        $pendingReservations = Reservation::query()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
        $upcomingReservations = Reservation::query()
            ->where('status', 'confirmed')
            ->where('entry_date', '>=', $today)
            ->latest()
            ->take(5)
            ->get();
        $recentReservations = Reservation::query()
            ->where('created_at', '>=', $today->copy()->subDays(7))
            ->latest()
            ->take(5)
            ->get();

        $blocks = DateBlock::query()
            ->where('entry_date', '>=', $monthStart)
            ->where('entry_date', '<=', $monthEnd)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'confirmedCount',
            'weeklyArrivals',
            'occupancyRate',
            'blocksCount',
            'cancelledCount',
            'pendingReservations',
            'upcomingReservations',
            'recentReservations',
        ));
    }

    public function edit(Reservation $reservation): View
    {
        return view('admin.reservations.edit', compact('reservation'));
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        return to_route('admin.reservations.index')
            ->with('success', 'La reserva se ha actualizado correctamente.');
    }
}
