<?php

namespace App\Filament\Resources\GoodsReceiptResource\Pages;

use App\Filament\Resources\GoodsReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class CreateGoodsReceipt extends CreateRecord
{

    protected static string $resource = GoodsReceiptResource::class;

    protected function afterCreate(): void
    {
        $receipt = $this->getRecord();

        DB::transaction(function () use ($receipt) {
            foreach ($receipt->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    ['warehouse_id' => $receipt->warehouse_id, 'product_variant_sku' => $item->product_variant_sku],
                    ['quantity' => 0]
                );
                $inventory->quantity += $item->quantity;
                $inventory->save();

                InventoryTransaction::create([
                    'warehouse_id' => $receipt->warehouse_id,
                    'product_variant_sku' => $item->product_variant_sku,
                    'type' => 'IN',
                    'quantity_change' => $item->quantity,
                    'reference_id' => $receipt->id,
                    'reference_type' => GoodsReceipt::class,
                    'user_id' => $receipt->user_id,
                    'notes' => 'Goods Receipt: '.$receipt->code,
                ]);
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }

}
