<?php

namespace Tests\Feature\Padel;

use App\Models\Booking;
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

class GameModeTest extends TestCase
{
    use RefreshDatabase;

    private User $lucas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00', Clock::ZONE));
        $this->lucas = User::where('email', 'lucas.martin@example.test')->first();
    }

    private function hold(string $court, string $time = '19:30'): Booking
    {
        return app(BookingService::class)->hold($this->lucas, Court::where('name', $court)->first(), Clock::slotStart('2026-10-23', $time));
    }

    private function pay(Booking $booking, array $data = [])
    {
        return $this->actingAs($this->lucas)->post("/bookings/{$booking->public_id}/pay", ['accept_terms' => '1'] + $data);
    }

    public function test_every_court_can_be_played_in_arcade_mode_for_14_euros_more(): void
    {
        foreach (['Terrain 1', 'Terrain 6'] as $court) {
            $booking = $this->hold($court);
            $this->assertSame(4800, $booking->price_cents);
            $this->pay($booking, ['game_mode' => 'arcade'])->assertRedirect();
            $booking->refresh();
            $this->assertSame('arcade', $booking->game_mode);
            $this->assertTrue($booking->is_arena);
            $this->assertSame(6200, $booking->price_cents);
            $this->assertSame(6200, DB::table('invoices')->where('booking_id', $booking->id)->value('total_cents'));
        }
    }

    public function test_off_peak_arcade_also_costs_14_euros_more(): void
    {
        $booking = $this->hold('Terrain 2', '10:30');
        $this->pay($booking, ['game_mode' => 'arcade']);
        $this->assertSame(3360 + 1400, $booking->refresh()->price_cents);
    }

    public function test_classic_is_the_default_and_keeps_the_normal_price(): void
    {
        $booking = $this->hold('Terrain 3');
        $this->pay($booking);
        $booking->refresh();
        $this->assertFalse($booking->is_arena);
        $this->assertNull($booking->game_mode);
        $this->assertSame(4800, DB::table('invoices')->where('booking_id', $booking->id)->value('total_cents'));
    }

    public function test_an_unknown_or_retired_game_mode_is_refused(): void
    {
        $booking = $this->hold('Terrain 4');
        // « duel » faisait partie des anciens scénarios, remplacés par le seul mode Arcade.
        foreach (['triche', 'duel'] as $mode) {
            $this->pay($booking, ['game_mode' => $mode])->assertSessionHasErrors('game_mode');
        }
        $this->assertSame('pending_payment', $booking->refresh()->status);
    }

    public function test_the_database_keeps_mode_and_arcade_flag_consistent(): void
    {
        $booking = $this->hold('Terrain 1');
        $this->expectException(QueryException::class);
        DB::table('bookings')->where('id', $booking->id)->update(['game_mode' => 'arcade']);
    }
}
