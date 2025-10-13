<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{

    protected static string $resource = SupplierResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Supplier Details')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Supplier Name')
                                            ->weight('bold')
                                            ->size('lg'),

                                        TextEntry::make('code')
                                            ->badge()
                                            ->copyable(),

                                        TextEntry::make('home_url')
                                            ->label('Website')
                                            ->icon('heroicon-o-globe-alt')
                                            ->url(fn($state): string => $state)
                                            ->openUrlInNewTab(),

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
                                            ->getStateUsing(fn(Supplier $record): int => $record->products()->count()),

                                        TextEntry::make('total_stock')
                                            ->label('Total Stock from Supplier')
                                            ->icon('heroicon-o-inbox-stack')
                                            ->getStateUsing(
                                                fn(Supplier $record): int => $record->products()->sum('quantity')
                                            ),
                                    ]),
                            ])->columnSpan(['lg' => 1]),
                    ]),
            ])->columns(2);
    }

}
