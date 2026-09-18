<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class PruneQaReservations extends Command
{
    protected $signature = 'reservations:prune-qa';

    protected $description = 'Delete reservations created by the e2e suite (name prefix "QA E2E")';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run in production.');

            return self::FAILURE;
        }

        $deleted = Reservation::query()
            ->where('name', 'like', 'QA E2E%')
            ->delete();

        $this->info("Deleted {$deleted} QA reservation(s).");

        return self::SUCCESS;
    }
}
