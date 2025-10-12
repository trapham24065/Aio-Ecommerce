<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\WarehouseResource\Widgets\WarehouseIncomeChart;
use App\Filament\Resources\WarehouseResource\Widgets\WarehouseStatsOverview;

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
                Section::make('Warehouse Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('code'),
                        TextEntry::make('street'),
                        TextEntry::make('city'),
                        TextEntry::make('state'),
                        TextEntry::make('postal_code'),
                        TextEntry::make('country'),
                    ]),

            ]);
    }

}
