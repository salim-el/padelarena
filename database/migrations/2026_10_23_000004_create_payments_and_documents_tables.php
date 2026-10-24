<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_player_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('payer_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('method', 15);
            $table->string('provider_reference')->nullable()->unique();
            $table->integer('amount_cents');
            $table->string('status', 20)->default('pending');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount_cents > 0)');

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->string('type', 12);
            $table->smallInteger('year');
            $table->integer('last_number')->default(0);
            $table->primary(['type', 'year']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestampTz('issued_at')->useCurrent();
            $table->string('customer_name', 120);
            $table->integer('net_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->integer('vat_cents');
            $table->integer('total_cents');
        });
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_total_check CHECK (total_cents = net_cents + vat_cents)');

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->timestampTz('issued_at')->useCurrent();
            $table->integer('net_cents');
            $table->integer('vat_cents');
            $table->integer('total_cents');
            $table->string('reason');
        });
        DB::statement('ALTER TABLE credit_notes ADD CONSTRAINT credit_notes_total_check CHECK (total_cents = net_cents + vat_cents)');

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->integer('amount_cents');
            $table->string('reason', 25);
            $table->string('status', 10)->default('pending');
            $table->foreignId('credit_note_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestampTz('created_at')->useCurrent();
        });

        // Une facture ou une note de crédit émise ne se modifie jamais et ne se supprime pas.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION forbid_change() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
              RAISE EXCEPTION '% est un document comptable : modification interdite', TG_TABLE_NAME;
            END $$;
            CREATE TRIGGER invoices_immutable BEFORE UPDATE OR DELETE ON invoices FOR EACH ROW EXECUTE FUNCTION forbid_change();
            CREATE TRIGGER credit_notes_immutable BEFORE UPDATE OR DELETE ON credit_notes FOR EACH ROW EXECUTE FUNCTION forbid_change();

            CREATE OR REPLACE FUNCTION check_refund_total() RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE paid integer; already integer;
            BEGIN
              SELECT amount_cents INTO paid FROM payments WHERE id = NEW.payment_id FOR UPDATE;
              SELECT COALESCE(SUM(amount_cents), 0) INTO already FROM refunds
               WHERE payment_id = NEW.payment_id AND status <> 'failed' AND id <> COALESCE(NEW.id, -1);
              IF already + NEW.amount_cents > paid THEN
                RAISE EXCEPTION 'Remboursement refusé : % + % dépasse le montant payé %', already, NEW.amount_cents, paid;
              END IF;
              RETURN NEW;
            END $$;
            CREATE TRIGGER refunds_total_check BEFORE INSERT OR UPDATE ON refunds FOR EACH ROW EXECUTE FUNCTION check_refund_total();
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('payments');
        DB::unprepared('DROP FUNCTION IF EXISTS forbid_change() CASCADE; DROP FUNCTION IF EXISTS check_refund_total() CASCADE;');
    }
};
