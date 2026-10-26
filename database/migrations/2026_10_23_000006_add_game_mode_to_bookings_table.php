<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('game_mode', 20)->nullable();
        });
        // Une partie arcade a toujours un mode, une partie classique jamais.
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_game_mode_check CHECK (is_arena = (game_mode IS NOT NULL) AND (game_mode IS NULL OR game_mode IN ('zone_hunt', 'duel', 'free')))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_game_mode_check');
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('game_mode');
        });
    }
};
