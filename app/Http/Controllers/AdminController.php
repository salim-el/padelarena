<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Services\Clock;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureStaff($request);
        $search = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? ''));
        $day = CarbonImmutable::now(Clock::ZONE)->startOfDay();
        $month = $day->startOfMonth();

        $today = Booking::with('court', 'organizer')
            ->where('starts_at', '>=', $day->utc())->where('starts_at', '<', $day->addDay()->utc())
            ->whereIn('status', ['pending_payment', 'confirmed', 'used', 'missed', 'cancelled_refunded', 'cancelled_unrefunded'])
            ->orderBy('starts_at')->orderBy('court_id')->get();
        $played = $today->whereIn('status', ['confirmed', 'used', 'missed'])->count();
        $slots = Court::where('is_active', true)->count() * count(Clock::SLOTS);

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'bookings_today' => $played,
                'occupancy' => $slots > 0 ? (int) round(100 * $played / $slots) : 0,
                'revenue_today_cents' => $this->revenue($day, $day->addDay()),
                'revenue_month_cents' => $this->revenue($month, $month->addMonth()),
            ],
            'today' => $today->map(fn (Booking $b) => [
                'id' => $b->public_id,
                'court' => $b->court->name,
                'time' => Clock::local($b->starts_at)->format('H:i').' – '.Clock::local($b->ends_at)->format('H:i'),
                'player' => $b->organizer->name,
                'status' => $b->status,
            ])->all(),
            'search' => $search,
            'users' => User::query()
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->whereLike('name', "%{$search}%")->orWhereLike('email', "%{$search}%")))
                ->orderBy('role')->orderBy('name')
                ->paginate(15)->withQueryString()->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'verified' => $u->email_verified_at !== null,
                'role' => $u->role,
                'status' => $u->status,
                'created_at' => $u->created_at?->setTimezone(Clock::ZONE)->format('d/m/Y'),
            ]),
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->ensurePlayer($request, $user);
        $user->forceFill(['status' => 'suspended'])->save();

        return back()->with('success', "Le compte de {$user->name} est suspendu. Il ne peut plus réserver.");
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->ensurePlayer($request, $user);
        $user->forceFill(['status' => 'active', 'suspended_until' => null])->save();

        return back()->with('success', "Le compte de {$user->name} est réactivé.");
    }

    // Chiffre d'affaires TTC encaissé : factures émises moins notes de crédit sur la période.
    private function revenue(CarbonImmutable $from, CarbonImmutable $to): int
    {
        $sum = fn (string $table) => (int) DB::table($table)->where('issued_at', '>=', $from->utc())->where('issued_at', '<', $to->utc())->sum('total_cents');

        return $sum('invoices') - $sum('credit_notes');
    }

    private function ensureStaff(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['manager', 'admin'], true), 403);
    }

    // Le personnel ne gère que les comptes joueurs : pas de suspension d'un collègue ni de soi-même.
    private function ensurePlayer(Request $request, User $user): void
    {
        $this->ensureStaff($request);
        abort_unless($user->role === 'player', 403);
    }
}
