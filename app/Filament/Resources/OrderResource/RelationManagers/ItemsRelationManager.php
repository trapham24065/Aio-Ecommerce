<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{

    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name'),
                TextColumn::make('sku'),
                TextColumn::make('quantity')->numeric(),
                TextColumn::make('price')->money('vnd'),
                TextColumn::make('total_price')->money('vnd'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

}
