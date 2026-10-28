<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('kind', 10);
            $table->integer('price_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->integer('stock');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_check CHECK (kind IN ('rental', 'sale') AND price_cents > 0 AND stock >= 0 AND vat_rate BETWEEN 0 AND 21)");

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->smallInteger('quantity');
            $table->integer('unit_price_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->unique(['booking_id', 'product_id']);
        });
        DB::statement('ALTER TABLE booking_items ADD CONSTRAINT booking_items_quantity_check CHECK (quantity BETWEEN 1 AND 4)');

        // Une facture peut mêler plusieurs taux : une ligne par article, chacune avec sa TVA.
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->string('label', 120);
            $table->smallInteger('quantity');
            $table->integer('unit_price_cents');
            $table->decimal('vat_rate', 4, 2);
            $table->integer('net_cents');
            $table->integer('vat_cents');
            $table->integer('total_cents');
        });
        DB::statement('ALTER TABLE invoice_lines ADD CONSTRAINT invoice_lines_total_check CHECK (total_cents = net_cents + vat_cents AND total_cents = quantity * unit_price_cents)');
        DB::unprepared('CREATE TRIGGER invoice_lines_immutable BEFORE UPDATE OR DELETE ON invoice_lines FOR EACH ROW EXECUTE FUNCTION forbid_change();');
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('products');
    }
};
