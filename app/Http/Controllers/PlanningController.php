<?php

namespace App\Http\Controllers;

use App\Services\Clock;
use App\Services\SlotService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanningController extends Controller
{
    public function __invoke(Request $request, SlotService $slots): Response
    {
        $today = CarbonImmutable::now(Clock::ZONE)->toDateString();
        $max = CarbonImmutable::now(Clock::ZONE)->addDays(30)->toDateString();
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$today, 'before_or_equal:'.$max]]);
        $date = $data['date'] ?? $today;

        return Inertia::render('planning', ['date' => $date, 'today' => $today, 'max' => $max] + $slots->forDate($date));
    }
}
