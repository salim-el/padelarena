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
        // Un mode de jeu n'a de sens que sur le terrain Arena.
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_game_mode_check CHECK (game_mode IS NULL OR (is_arena AND game_mode IN ('chasse_aux_zones', 'duel', 'jeu_libre')))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_game_mode_check');
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('game_mode');
        });
    }
};
