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
            $table->string('account_type', 10)->default('individual');
            $table->string('company_name', 120)->nullable();
            $table->char('vat_number', 12)->nullable();
        });
        // Une entreprise a toujours sa dénomination et son numéro de TVA ; un particulier n'en a jamais.
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_account_type_check CHECK (
            (account_type = 'individual' AND company_name IS NULL AND vat_number IS NULL)
            OR (account_type = 'company' AND company_name IS NOT NULL AND vat_number ~ '^BE[01][0-9]{9}$'))");
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_company', 120)->nullable();
            $table->char('customer_vat', 12)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['customer_company', 'customer_vat']));
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_account_type_check');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['account_type', 'company_name', 'vat_number']));
    }
};
