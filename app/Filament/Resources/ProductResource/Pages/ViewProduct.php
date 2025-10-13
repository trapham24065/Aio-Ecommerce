<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use App\Models\Product;

class ViewProduct extends ViewRecord
{

    protected static string $resource = ProductResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make()->schema([
                            ImageEntry::make('thumbnail')
                                ->hiddenLabel()
                                ->columnSpanFull(),
                        ]),

                        Section::make('Product Details')
                            ->schema([
                                TextEntry::make('description')
                                    ->markdown()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Details')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Product Name')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('type')
                                    ->badge()
                                    ->color(
                                        fn($state): string => $state === Product::TYPE_SIMPLE ? 'primary' : 'success'
                                    ),

                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn($state): string => $state ? 'Active' : 'Inactive')
                                    ->color(fn($state): string => $state ? 'success' : 'danger'),
                            ]),

                        Section::make('Pricing & Stock')
                            ->schema([

                                TextEntry::make('sku')
                                    ->visible(fn(Product $record): bool => $record->type === Product::TYPE_SIMPLE),
                                TextEntry::make('base_cost')
                                    ->money('vnd')
                                    ->visible(fn(Product $record): bool => $record->type === Product::TYPE_SIMPLE),

                                TextEntry::make('variants_count')
                                    ->label('Number of Variants')
                                    ->getStateUsing(fn(Product $record): int => $record->variants()->count())
                                    ->visible(fn(Product $record): bool => $record->type === Product::TYPE_VARIANT),

                                TextEntry::make('quantity')
                                    ->label('Total Stock'),
                            ])->columns(2),

                        Section::make('Associations')
                            ->schema([
                                TextEntry::make('category.name'),
                                TextEntry::make('brand.name'),
                                TextEntry::make('supplier.name'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

}
