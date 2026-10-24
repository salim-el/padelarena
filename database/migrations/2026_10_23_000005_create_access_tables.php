<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_player_id')->unique()->constrained()->restrictOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('valid_from');
            $table->timestampTz('valid_until');
            $table->timestampTz('used_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->foreignId('access_pass_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('result', 10);
            $table->string('refusal_reason', 20)->nullable();
            $table->timestampTz('scanned_at')->useCurrent();
        });
        DB::statement("ALTER TABLE access_logs ADD CONSTRAINT access_logs_reason_check CHECK ((result = 'refused') = (refusal_reason IS NOT NULL))");
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('access_passes');
    }
};
