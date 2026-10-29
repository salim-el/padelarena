<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Un remboursement en échec ne rend pas d'argent : il ne doit pas compter dans le plafond du montant payé.
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION check_refund_total() RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE paid integer; already integer;
            BEGIN
              IF NEW.status = 'failed' THEN
                RETURN NEW;
              END IF;
              SELECT amount_cents INTO paid FROM payments WHERE id = NEW.payment_id FOR UPDATE;
              SELECT COALESCE(SUM(amount_cents), 0) INTO already FROM refunds
               WHERE payment_id = NEW.payment_id AND status <> 'failed' AND id <> COALESCE(NEW.id, -1);
              IF already + NEW.amount_cents > paid THEN
                RAISE EXCEPTION 'Remboursement refusé : % + % dépasse le montant payé %', already, NEW.amount_cents, paid;
              END IF;
              RETURN NEW;
            END $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
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
        SQL);
    }
};
