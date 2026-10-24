<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->unique();
            $table->boolean('is_arena')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('price_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->boolean('is_arena')->default(false);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestampsTz();
        });
        DB::statement('ALTER TABLE pricing_rules ADD CONSTRAINT pricing_rules_price_check CHECK (price_cents > 0 AND end_time > start_time AND vat_rate BETWEEN 0 AND 21)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
        Schema::dropIfExists('courts');
    }
};
