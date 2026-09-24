<?php

namespace App\Console\Commands;

use App\Models\DateBlock;
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

        $blockDeleted = DateBlock::query()
            ->where('reason', 'like', '%e2e%')
            ->delete();

        $this->info("Deleted {$deleted} QA reservation(s) and {$blockDeleted} QA block(s).");

        return self::SUCCESS;
    }
}
