<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewBrand extends ViewRecord
{

    protected static string $resource = BrandResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Brand Details')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Brand Name')
                                            ->weight('bold')
                                            ->size('lg'),

                                        TextEntry::make('code')
                                            ->badge()
                                            ->copyable(),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->formatStateUsing(fn($state): string => $state ? 'Active' : 'Inactive')
                                            ->color(fn($state): string => $state ? 'success' : 'danger'),
                                    ]),
                            ])->columnSpan(['lg' => 1]),
                        Group::make()
                            ->schema([
                                Section::make('Analytics')
                                    ->schema([
                                        TextEntry::make('products_count')
                                            ->label('Total Products')
                                            ->icon('heroicon-o-squares-2x2')
                                            ->getStateUsing(fn(Brand $record): int => $record->products()->count()),

                                        TextEntry::make('total_stock')
                                            ->label('Total Stock in Brand')
                                            ->icon('heroicon-o-inbox-stack')
                                            ->getStateUsing(
                                                fn(Brand $record): int => $record->products()->sum('quantity')
                                            ),
                                    ]),
                            ])->columnSpan(['lg' => 1]),

                    ])->columns(2),
            ]);
    }

}
