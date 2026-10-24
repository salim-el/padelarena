<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pricing_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->string('status', 25)->default('pending_payment');
            $table->integer('price_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->boolean('is_arena')->default(false);
            $table->timestampTz('hold_expires_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('pending_payment', 'expired', 'confirmed', 'used', 'missed', 'cancelled_refunded', 'cancelled_unrefunded', 'frozen'))");
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_time_check CHECK (ends_at > starts_at AND price_cents >= 0)');
        // Deux réservations actives ne peuvent jamais se chevaucher sur un même terrain.
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_no_overlap EXCLUDE USING gist (court_id WITH =, tstzrange(starts_at, ends_at, '[)') WITH &&) WHERE (status IN ('pending_payment', 'confirmed', 'used', 'frozen'))");

        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('from_status', 25)->nullable();
            $table->string('to_status', 25);
            $table->string('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('booking_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('guest_email')->nullable();
            $table->boolean('is_organizer')->default(false);
            $table->integer('share_cents');
            $table->string('status', 20)->default('invited');
            $table->timestampTz('joined_at')->useCurrent();
            $table->unique(['booking_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_players');
        Schema::dropIfExists('booking_status_histories');
        Schema::dropIfExists('bookings');
    }
};
