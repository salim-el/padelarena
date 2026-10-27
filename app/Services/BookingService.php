<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\AccessPass;
use App\Models\Booking;
use App\Models\BookingPlayer;
use App\Models\Court;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public const HOLD_MINUTES = 10;

    public const MAX_ACTIVE = 3;

    public const FREE_CANCELLATION_HOURS = 24;

    public function __construct(private SlotService $slots)
    {
    }

    /** Libère les créneaux bloqués dont les dix minutes sont écoulées. */
    public function expireHolds(): int
    {
        $ids = Booking::where('status', 'pending_payment')->where('hold_expires_at', '<', now())->pluck('id');
        foreach ($ids as $id) {
            $this->transition(Booking::find($id), 'expired', 'Dix minutes sans paiement.');
        }

        return $ids->count();
    }

    public function hold(User $user, Court $court, CarbonImmutable $startUtc): Booking
    {
        if ($user->status !== 'active' || $user->email_verified_at === null) {
            throw new BookingException('Votre compte doit être actif et votre e-mail confirmé pour réserver.');
        }
        if (! in_array(Clock::local($startUtc)->format('H:i'), Clock::SLOTS, true) || ! $startUtc->isFuture() || $startUtc->gt(now()->addDays(30))) {
            throw new BookingException('Ce créneau n’existe pas ou n’est plus réservable.');
        }
        $rule = $this->slots->priceFor($startUtc);
        if ($rule === null || ! $court->is_active) {
            throw new BookingException('Ce terrain n’est pas réservable à cette heure.');
        }

        $this->expireHolds();

        return DB::transaction(function () use ($user, $court, $startUtc, $rule) {
            $active = Booking::where('organizer_id', $user->id)->whereIn('status', ['pending_payment', 'confirmed'])->where('starts_at', '>', now())->count();
            if ($active >= self::MAX_ACTIVE) {
                throw new BookingException('Vous avez déjà trois réservations à venir.');
            }
            try {
                // Point de sauvegarde : un refus de la contrainte ne casse pas la transaction englobante.
                $booking = DB::transaction(fn () => Booking::create([
                    'public_id' => (string) Str::uuid(),
                    'court_id' => $court->id,
                    'organizer_id' => $user->id,
                    'pricing_rule_id' => $rule->id,
                    'starts_at' => $startUtc,
                    'ends_at' => $startUtc->addMinutes(Clock::SLOT_MINUTES),
                    'status' => 'pending_payment',
                    'price_cents' => $this->slots->priceCents($rule, $startUtc),
                    'vat_rate' => $rule->vat_rate,
                    'is_arena' => false,
                    'hold_expires_at' => now()->addMinutes(self::HOLD_MINUTES),
                ]));
            } catch (QueryException $e) {
                // 23P01 : la contrainte d'exclusion refuse un chevauchement.
                if ($e->getCode() === '23P01') {
                    throw new BookingException('Ce créneau vient d’être réservé par quelqu’un d’autre.');
                }
                throw $e;
            }
            BookingPlayer::create(['booking_id' => $booking->id, 'user_id' => $user->id, 'is_organizer' => true, 'share_cents' => $booking->price_cents, 'status' => 'invited']);
            $this->history($booking, null, 'pending_payment', 'Réservation créée.', $user->id);

            return $booking;
        });
    }

    /** Paiement simulé de l'alpha. En production, la confirmation viendra de la notification signée de Stripe. */
    public function confirmPayment(Booking $booking, User $user, ?string $gameMode = null): Booking
    {
        return DB::transaction(function () use ($booking, $user, $gameMode) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($user->status !== 'active') {
                throw new BookingException('Votre compte est suspendu. Le paiement est refusé.');
            }
            if ($booking->status !== 'pending_payment' || $booking->hold_expires_at?->isPast()) {
                throw new BookingException('Les dix minutes sont écoulées. Le créneau a été libéré, rien n’a été débité.');
            }
            $player = $booking->players()->where('is_organizer', true)->firstOrFail();
            // Un mode arcade remplace le prix classique par le tarif arcade du même créneau (+14 €).
            if ($gameMode !== null && $gameMode !== 'classic') {
                $rule = $this->slots->priceFor($booking->starts_at, true);
                $supplement = $this->slots->arcadeSupplement($booking->starts_at);
                if ($rule === null || $supplement === null) {
                    throw new BookingException('Le mode arcade n’est pas disponible sur ce créneau.');
                }
                // Le supplément s'ajoute au prix déjà bloqué, remise flash comprise.
                $price = $booking->price_cents + $supplement;
                $booking->update(['is_arena' => true, 'game_mode' => $gameMode, 'pricing_rule_id' => $rule->id, 'price_cents' => $price, 'vat_rate' => $rule->vat_rate]);
                $player->update(['share_cents' => $price]);
            }
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'booking_player_id' => $player->id,
                'payer_id' => $user->id,
                'method' => 'card',
                'provider_reference' => 'test_'.Str::uuid(),
                'amount_cents' => $player->share_cents,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);
            $net = (int) round($payment->amount_cents / (1 + (float) $booking->vat_rate / 100));
            Invoice::create([
                'number' => $this->nextNumber('invoice', 'F'),
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'issued_at' => now(),
                'customer_name' => $user->name,
                'net_cents' => $net,
                'vat_rate' => $booking->vat_rate,
                'vat_cents' => $payment->amount_cents - $net,
                'total_cents' => $payment->amount_cents,
            ]);
            $player->update(['status' => 'paid']);
            AccessPass::create([
                'booking_player_id' => $player->id,
                'token_hash' => hash('sha256', self::passToken($player)),
                'valid_from' => $booking->starts_at->subMinutes(15),
                'valid_until' => $booking->ends_at,
            ]);
            $booking->update(['hold_expires_at' => null]);
            $this->transition($booking, 'confirmed', 'Paiement reçu.', $user->id);

            return $booking->fresh();
        });
    }

    public function cancel(Booking $booking, User $user): Booking
    {
        return DB::transaction(function () use ($booking, $user) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'confirmed') {
                throw new BookingException('Cette réservation ne peut plus être annulée.');
            }
            if (! $booking->starts_at->isFuture()) {
                throw new BookingException('Le créneau a commencé. Seule une demande exceptionnelle reste possible.');
            }
            $refundable = now()->lte($booking->starts_at->subHours(self::FREE_CANCELLATION_HOURS));
            if ($refundable) {
                foreach ($booking->payments()->where('status', 'succeeded')->get() as $payment) {
                    $invoice = Invoice::where('payment_id', $payment->id)->firstOrFail();
                    $note = CreditNote::create([
                        'number' => $this->nextNumber('credit_note', 'NC'),
                        'invoice_id' => $invoice->id,
                        'issued_at' => now(),
                        'net_cents' => $invoice->net_cents,
                        'vat_cents' => $invoice->vat_cents,
                        'total_cents' => $invoice->total_cents,
                        'reason' => 'Annulation au moins 24 h avant le match.',
                    ]);
                    Refund::create(['payment_id' => $payment->id, 'amount_cents' => $payment->amount_cents, 'reason' => 'cancellation_24h', 'status' => 'succeeded', 'credit_note_id' => $note->id]);
                    $payment->update(['status' => 'refunded']);
                }
            }
            AccessPass::whereIn('booking_player_id', $booking->players()->pluck('id'))->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $booking->update(['cancelled_at' => now()]);
            $this->transition($booking, $refundable ? 'cancelled_refunded' : 'cancelled_unrefunded', $refundable ? 'Annulation au moins 24 h avant.' : 'Annulation moins de 24 h avant.', $user->id);

            return $booking->fresh();
        });
    }

    /** Jeton du QR code, recalculable à l'affichage. Seule son empreinte est stockée. */
    public static function passToken(BookingPlayer $player): string
    {
        return hash_hmac('sha256', 'booking-player:'.$player->id, (string) config('app.key'));
    }

    /** Numérotation continue par année : l'upsert verrouille la ligne du compteur jusqu'à la fin de la transaction. */
    private function nextNumber(string $type, string $prefix): string
    {
        $year = (int) now()->format('Y');
        $row = DB::selectOne(
            'INSERT INTO document_sequences (type, year, last_number) VALUES (?, ?, 1)
             ON CONFLICT (type, year) DO UPDATE SET last_number = document_sequences.last_number + 1
             RETURNING last_number',
            [$type, $year]
        );

        return sprintf('%s%d-%06d', $prefix, $year, $row->last_number);
    }

    private function transition(Booking $booking, string $to, string $reason, ?int $by = null): void
    {
        $from = $booking->status;
        $booking->update(['status' => $to]);
        $this->history($booking, $from, $to, $reason, $by);
    }

    private function history(Booking $booking, ?string $from, string $to, string $reason, ?int $by): void
    {
        DB::table('booking_status_histories')->insert([
            'booking_id' => $booking->id, 'from_status' => $from, 'to_status' => $to, 'reason' => $reason, 'changed_by' => $by, 'created_at' => now(),
        ]);
    }
}
