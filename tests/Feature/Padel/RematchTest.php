<?php

namespace Tests\Feature\Padel;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Services\BookingService;
use App\Services\Clock;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RematchTest extends TestCase
{
    use RefreshDatabase;

    private User $lucas;

    private Booking $played;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00', Clock::ZONE));
        $this->lucas = User::where('email', 'lucas.martin@example.test')->first();
        $service = app(BookingService::class);
        $this->played = $service->confirmPayment($service->hold($this->lucas, Court::where('name', 'Terrain 3')->first(), Clock::slotStart('2026-10-23', '19:30')), $this->lucas);
    }

    public function test_rematch_holds_the_same_court_and_local_hour_one_week_later_across_the_time_change(): void
    {
        $this->actingAs($this->lucas)->post("/bookings/{$this->played->public_id}/rematch")->assertRedirectContains('/checkout');
        $next = Booking::where('organizer_id', $this->lucas->id)->where('status', 'pending_payment')->firstOrFail();
        $this->assertSame($this->played->court_id, $next->court_id);
        // 23 octobre 19:30 (heure d'été) puis 30 octobre 19:30 (heure d'hiver).
        $this->assertSame('2026-10-30 19:30', Clock::local($next->starts_at)->format('Y-m-d H:i'));
    }

    public function test_rematch_on_a_taken_slot_explains_why(): void
    {
        $other = User::where('email', 'ines.laurent@example.test')->first();
        app(BookingService::class)->hold($other, Court::where('name', 'Terrain 3')->first(), Clock::slotStart('2026-10-30', '19:30'));
        $this->actingAs($this->lucas)->post("/bookings/{$this->played->public_id}/rematch")->assertSessionHasErrors('booking');
    }

    public function test_another_player_cannot_rematch_my_booking(): void
    {
        $other = User::where('email', 'ines.laurent@example.test')->first();
        $this->actingAs($other)->post("/bookings/{$this->played->public_id}/rematch")->assertNotFound();
    }
}
