<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;

class InventoryObserver
{

    private function updateTotalStock(Inventory $inventory): void
    {
        $sku = $inventory->product_variant_sku;

        $product = null;

        $variant = ProductVariant::where('sku', $sku)->with('product')->first();

        if ($variant) {
            $variant->quantity = $variant->total_stock;
            $variant->saveQuietly();
            $product = $variant->product;
        } else {
            $product = Product::where('sku', $sku)
                ->where('type', Product::TYPE_SIMPLE)
                ->first();
        }

        if ($product) {
            $totalStock = $product->total_stock;

            $product->quantity = $totalStock;
            $product->saveQuietly();
        }
    }

    /**
     * Handle the Inventory "saved" event.
     */
    public function saved(Inventory $inventory): void
    {
        $this->updateTotalStock($inventory);
    }

    /**
     * Handle the Inventory "deleted" event.
     */
    public function deleted(Inventory $inventory): void
    {
        $this->updateTotalStock($inventory);
    }

}
