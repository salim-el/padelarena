<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable();
            $table->char('locale', 2)->default('fr');
            $table->string('role', 20)->default('player');
            $table->string('status', 20)->default('active');
            $table->decimal('level', 3, 1)->nullable();
            $table->timestampTz('suspended_until')->nullable();
            $table->timestampTz('deactivated_at')->nullable();
            $table->timestampTz('anonymized_at')->nullable();
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('player', 'manager', 'admin'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('pending', 'active', 'suspended', 'revoked', 'deactivated', 'anonymized'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'locale', 'role', 'status', 'level', 'suspended_until', 'deactivated_at', 'anonymized_at']);
        });
    }
};
