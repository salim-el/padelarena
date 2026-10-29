<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Les trois scénarios (chasse aux zones, duel, jeu libre) laissent la place à un seul mode Arcade :
    // règles du padel, zones Or, Rouge et Bleue en miroir.
    public function up(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_game_mode_check');
        DB::table('bookings')->whereNotNull('game_mode')->update(['game_mode' => 'arcade']);
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_game_mode_check CHECK (is_arena = (game_mode IS NOT NULL) AND (game_mode IS NULL OR game_mode = 'arcade'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_game_mode_check');
        DB::table('bookings')->whereNotNull('game_mode')->update(['game_mode' => 'free']);
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_game_mode_check CHECK (is_arena = (game_mode IS NOT NULL) AND (game_mode IS NULL OR game_mode IN ('zone_hunt', 'duel', 'free')))");
    }
};
