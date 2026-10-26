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

    private function hold(string $court): Booking
    {
        return app(BookingService::class)->hold($this->lucas, Court::where('name', $court)->first(), Clock::slotStart('2026-10-23', '19:30'));
    }

    public function test_the_player_chooses_the_arena_game_mode_when_paying(): void
    {
        $booking = $this->hold('Terrain 4');
        $this->actingAs($this->lucas)->post("/bookings/{$booking->public_id}/pay", ['accept_terms' => '1', 'game_mode' => 'duel'])->assertRedirect();
        $this->assertSame('duel', $booking->refresh()->game_mode);
    }

    public function test_arena_defaults_to_free_play_and_classic_courts_have_no_mode(): void
    {
        $arena = $this->hold('Terrain 4');
        $this->actingAs($this->lucas)->post("/bookings/{$arena->public_id}/pay", ['accept_terms' => '1']);
        $this->assertSame('jeu_libre', $arena->refresh()->game_mode);

        $classic = $this->hold('Terrain 1');
        $this->actingAs($this->lucas)->post("/bookings/{$classic->public_id}/pay", ['accept_terms' => '1', 'game_mode' => 'duel']);
        $this->assertNull($classic->refresh()->game_mode);
        $this->assertSame('confirmed', $classic->status);
    }

    public function test_an_unknown_game_mode_is_refused(): void
    {
        $booking = $this->hold('Terrain 4');
        $this->actingAs($this->lucas)->post("/bookings/{$booking->public_id}/pay", ['accept_terms' => '1', 'game_mode' => 'triche'])->assertSessionHasErrors('game_mode');
        $this->assertSame('pending_payment', $booking->refresh()->status);
    }

    public function test_the_database_refuses_a_game_mode_on_a_classic_court(): void
    {
        $booking = $this->hold('Terrain 1');
        $this->expectException(QueryException::class);
        DB::table('bookings')->where('id', $booking->id)->update(['game_mode' => 'duel']);
    }
}
