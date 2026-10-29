<?php

namespace Tests\Feature\Padel;

use App\Models\Court;
use App\Models\User;
use App\Services\BookingService;
use App\Services\Clock;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefundTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_failed_refund_does_not_count_in_the_paid_amount_ceiling(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00', Clock::ZONE));
        $lucas = User::where('email', 'lucas.martin@example.test')->first();
        $service = app(BookingService::class);
        $booking = $service->confirmPayment($service->hold($lucas, Court::where('name', 'Terrain 1')->first(), Clock::slotStart('2026-10-23', '19:30')), $lucas);
        $payment = DB::table('payments')->where('booking_id', $booking->id)->value('id');
        $refund = fn (int $cents, string $status) => DB::table('refunds')->insert(['payment_id' => $payment, 'amount_cents' => $cents, 'reason' => 'cancellation_24h', 'status' => $status]);

        // 4 800 payés : 3 000 remboursés, puis 2 000 en échec, ce qui ne rend rien.
        $refund(3000, 'succeeded');
        $refund(2000, 'failed');
        $refund(1800, 'succeeded');
        $this->assertSame(4800, (int) DB::table('refunds')->where('status', 'succeeded')->sum('amount_cents'));

        // Un centime de plus dépasse le montant payé.
        $this->expectException(QueryException::class);
        $refund(1, 'succeeded');
    }
}
