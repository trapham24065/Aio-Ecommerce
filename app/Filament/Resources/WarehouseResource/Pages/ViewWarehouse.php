<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\WarehouseResource\Widgets\WarehouseIncomeChart;
use App\Filament\Resources\WarehouseResource\Widgets\WarehouseStatsOverview;
use App\Models\Warehouse;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Grid;

class ViewWarehouse extends ViewRecord
{

    protected static string $resource = WarehouseResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            WarehouseStatsOverview::class,
            WarehouseIncomeChart::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Warehouse Details')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Warehouse Name')
                                    ->icon('heroicon-o-home-modern'),
                                TextEntry::make('code')
                                    ->label('Code')
                                    ->icon('heroicon-o-hashtag')
                                    ->badge()
                                    ->copyable()
                                    ->copyMessage('Code copied!'),
                            ])->columns(2),

                        Section::make('Address Details')
                            ->schema([
                                TextEntry::make('formatted_address')
                                    ->label('Full Address')
                                    ->icon('heroicon-o-map-pin')
                                    ->getStateUsing(function (Warehouse $record): string {
                                        return collect([
                                            $record->street,
                                            $record->city,
                                            $record->state,
                                            $record->postal_code,
                                            $record->country,
                                        ])->filter()->join(', ');
                                    }),
                            ]),
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Status & Timestamps')
                            ->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn($state): string => $state ? 'Active' : 'Inactive')
                                    ->color(fn($state): string => $state ? 'success' : 'danger'),

                                TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime()
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->since()
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

}
