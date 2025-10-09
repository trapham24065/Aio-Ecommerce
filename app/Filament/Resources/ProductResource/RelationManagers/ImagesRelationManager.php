<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImagesRelationManager extends RelationManager
{

    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('url')
                    ->label('Image')
                    ->image()
                    ->directory('product-images')
                    ->required(),
                Select::make('product_variant_id')
                    ->label('Assign to Variant (Optional)')
                    ->relationship('variant', 'sku')
                    ->options(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()->variants->pluck('sku', 'id');
                    })
                    ->placeholder('General Product Image'),

                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),

                TextInput::make('position')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('url')
            ->columns([
                Tables\Columns\TextColumn::make('url'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
