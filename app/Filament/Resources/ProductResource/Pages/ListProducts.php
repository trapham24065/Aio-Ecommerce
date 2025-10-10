<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Tables\Table;

class ListProducts extends ListRecords
{

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Products')
                ->badge(Product::query()->count()),

            'this_week' => Tab::make('This Week')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                )
                ->badge(
                    Product::query()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
                ),

            'this_month' => Tab::make('This Month')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->whereBetween(
                        'created_at',
                        [now()->startOfMonth(), now()->endOfMonth()]
                    )
                )
                ->badge(
                    Product::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()
                ),

            'this_year' => Tab::make('This Year')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereYear('created_at', now()->year))
                ->badge(Product::query()->whereYear('created_at', now()->year)->count()),
        ];
    }

}
