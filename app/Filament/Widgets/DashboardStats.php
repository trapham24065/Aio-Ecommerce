<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Inventory;

class DashboardStats extends BaseWidget
{

    protected static ?int $columns = 3;

    protected function getStats(): array
    {
        $totalProducts = Product::where('status', true)->count();

        $totalStock = Inventory::sum('quantity');

        $lowStockCount = Product::where('quantity', '<=', 5)->where('quantity', '>', 0)->count();

        return [
            Stat::make('Total Active Products', $totalProducts)
                ->description('Number of products available for sale')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Total Stock Quantity', number_format($totalStock))
                ->description('Total quantity of all items in all warehouses')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('info'),

            Stat::make('Low Stock Products', $lowStockCount)
                ->description('Products with stock quantity <= 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }

}
