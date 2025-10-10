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

class ViewProduct extends ViewRecord
{

    protected static string $resource = ProductResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('thumbnail')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                        TextEntry::make('name'),
                        TextEntry::make('sku'),
                        TextEntry::make('category.name'),
                        TextEntry::make('brand.name'),
                        TextEntry::make('base_cost')->money('vnd'),
                        TextEntry::make('quantity'),
                        TextEntry::make('description')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }

}
