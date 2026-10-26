<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\PricingRule;
use Carbon\CarbonImmutable;

class SlotService
{
    /** Règle de prix d'un créneau : classique, ou arcade avec son supplément. */
    public function priceFor(CarbonImmutable $startUtc, bool $arcade = false): ?PricingRule
    {
        $local = Clock::local($startUtc);

        return PricingRule::query()
            ->where('is_arena', $arcade)
            ->where('start_time', '<=', $local->format('H:i:s'))
            ->where('end_time', '>', $local->format('H:i:s'))
            ->whereDate('valid_from', '<=', $local->toDateString())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $local->toDateString()))
            ->orderByDesc('valid_from')
            ->first();
    }

    /** Créneaux d'un jour, terrain par terrain, sans aucune donnée personnelle. */
    public function forDate(string $date): array
    {
        $courts = Court::where('is_active', true)->orderBy('id')->get();
        $dayStart = Clock::slotStart($date, '00:00');
        $busy = Booking::query()
            ->whereIn('status', Booking::ACTIVE)
            ->where('starts_at', '<', $dayStart->addDay())
            ->where('ends_at', '>', $dayStart)
            ->where(fn ($q) => $q->where('status', '!=', 'pending_payment')->orWhere('hold_expires_at', '>', now()))
            ->get(['court_id', 'starts_at', 'ends_at']);

        $rows = [];
        foreach (Clock::SLOTS as $time) {
            $start = Clock::slotStart($date, $time);
            $end = $start->addMinutes(Clock::SLOT_MINUTES);
            $cells = [];
            foreach ($courts as $court) {
                $taken = $busy->contains(fn ($b) => $b->court_id === $court->id && $b->starts_at < $end && $b->ends_at > $start);
                $rule = $this->priceFor($start);
                $cells[] = [
                    'court_id' => $court->id,
                    'available' => ! $taken && $rule !== null && $start->isFuture(),
                    'price_cents' => $rule?->price_cents,
                    'past' => ! $start->isFuture(),
                    'off_peak' => $rule !== null && str_contains(strtolower($rule->name), 'creuses'),
                ];
            }
            $rows[] = ['time' => $time, 'end' => Clock::local($end)->format('H:i'), 'starts_at' => $start->toIso8601String(), 'cells' => $cells];
        }

        return [
            'courts' => $courts->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'is_arena' => $c->is_arena])->all(),
            'rows' => $rows,
        ];
    }
}
