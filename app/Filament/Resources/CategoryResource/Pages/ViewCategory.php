<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{

    protected static string $resource = CategoryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Category Details')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Category Name')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->color('primary'),

                                        TextEntry::make('code')
                                            ->label('Category Code')
                                            ->badge()
                                            ->copyable(),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->label('Status')
                                            ->formatStateUsing(fn($state): string => $state ? 'Active' : 'Inactive')
                                            ->color(fn($state): string => $state ? 'success' : 'danger'),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),

                        Group::make()
                            ->schema([
                                Section::make('Category Analytics')
                                    ->schema([
                                        TextEntry::make('products_count')
                                            ->label('Total Products')
                                            ->icon('heroicon-o-squares-2x2')
                                            ->getStateUsing(fn(Category $record): int => $record->products()->count()),

                                        TextEntry::make('total_stock')
                                            ->label('Total Stock in Category')
                                            ->icon('heroicon-o-inbox-stack')
                                            ->getStateUsing(
                                                fn(Category $record): int => $record->products()->sum('quantity')
                                            ),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])->columns(2),

            ]);
    }

}
