<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;

class ListWarehouses extends ListRecords
{

    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('createGoodsReceipt')
                ->label('Create Goods Receipt')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('warehouse_id')
                        ->label('Warehouse')
                        ->options(Warehouse::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Textarea::make('notes')->nullable(),

                    Repeater::make('items')
                        ->schema([
                            Select::make('product_variant_sku')
                                ->label('Product SKU')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search) {
                                    $variantSkus = ProductVariant::where('sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('sku', 'sku');

                                    $simpleSkus = Product::where('type', Product::TYPE_SIMPLE)
                                        ->where('sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('sku', 'sku');

                                    return $variantSkus->merge($simpleSkus)->toArray();
                                })
                                ->required(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->required(),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        foreach ($data['items'] as $item) {
                            $inventory = Inventory::firstOrCreate(
                                [
                                    'warehouse_id'        => $data['warehouse_id'],
                                    'product_variant_sku' => $item['product_variant_sku'],
                                ],
                                ['quantity' => 0]
                            );
                            $inventory->quantity += $item['quantity'];

                            $inventory->save();

                            InventoryTransaction::create([
                                'warehouse_id'        => $data['warehouse_id'],
                                'product_variant_sku' => $item['product_variant_sku'],
                                'type'                => 'IN',
                                'quantity_change'     => $item['quantity'],
                                'notes'               => $data['notes'],
                                'user_id'             => auth()->id(),
                                'reference_id'        => null,
                                'reference_type'      => null,
                            ]);
                        }
                    });
                    Notification::make()->title('Goods receipt created successfully!')->success()->send();
                }),
        ];
    }

}
