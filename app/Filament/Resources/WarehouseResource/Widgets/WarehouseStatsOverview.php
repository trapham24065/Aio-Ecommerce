<?php

namespace App\Filament\Resources\WarehouseResource\Widgets;

use App\Models\Inventory;
use App\Models\Warehouse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WarehouseStatsOverview extends BaseWidget
{

    protected static ?int $columns = 2;

    protected int|string|array $columnSpan = ['md' => 12];

    public ?Warehouse $record = null;

    protected function getStats(): array
    {
        $uniqueProductCount = Inventory::where('warehouse_id', $this->record->id)
            ->where('quantity', '>', 0)
            ->count();

        $totalStock = Inventory::where('warehouse_id', $this->record->id)->sum('quantity');

        return [
            Stat::make('Unique Products (SKUs)', $uniqueProductCount)
                ->description('Total distinct products in stock')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Total Stock Quantity', $totalStock)
                ->description('Total quantity of all items')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('info'),
        ];
    }

}
