<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['product_variant_sku']);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['product_variant_sku']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('product_variant_sku')->references('sku')->on('product_variants')->cascadeOnDelete();
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('product_variant_sku')->references('sku')->on('product_variants')->cascadeOnDelete();
        });
    }

};
