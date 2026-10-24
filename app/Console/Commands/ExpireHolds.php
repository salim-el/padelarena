<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireHolds extends Command
{
    protected $signature = 'bookings:expire-holds';

    protected $description = 'Libère les créneaux bloqués depuis plus de dix minutes sans paiement';

    public function handle(BookingService $bookings): int
    {
        $this->info($bookings->expireHolds().' créneau(x) libéré(s).');

        return self::SUCCESS;
    }
}
