<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;

class OrderObserver
{

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'processing') {
            foreach ($order->items as $item) {
                $inventory = Inventory::where('product_variant_sku', $item->sku)->first();
                if ($inventory) {
                    $inventory->quantity -= $item->quantity;
                    $inventory->save(); // Kích hoạt InventoryObserver
                }

                InventoryTransaction::create([
                    'type'            => 'OUT',
                    'quantity_change' => -$item->quantity,
                    'reference_id'    => $order->id,
                    'reference_type'  => Order::class,
                ]);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

}
