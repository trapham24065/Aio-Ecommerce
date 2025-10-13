<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('code')->unique();
            $table->text('notes')->nullable();
            $table->date('receipt_date');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->string('product_variant_sku');
            $table->unsignedInteger('quantity');
        });
    }

};
