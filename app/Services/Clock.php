<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class Clock
{
    public const ZONE = 'Europe/Brussels';

    public const SLOTS = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:30', '21:00'];

    public const SLOT_MINUTES = 90;

    public static function local(CarbonImmutable $utc): CarbonImmutable
    {
        return $utc->setTimezone(self::ZONE);
    }

    // Heure locale de Bruxelles vers temps universel.
    public static function slotStart(string $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i', "$date $time", self::ZONE)->utc();
    }
}
