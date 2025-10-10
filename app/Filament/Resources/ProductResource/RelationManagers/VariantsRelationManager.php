<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariantsRelationManager extends RelationManager
{

    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        /** @var Product $product */
        $product = $this->getOwnerRecord();

        $schema = [
            TextInput::make('sku')
                ->required()
                ->maxLength(255),
            TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('đ')
                ->minValue(1),
            TextInput::make('quantity')
                ->required()
                ->numeric()
                ->default(0)
                ->minValue(1),
        ];

        foreach ($product->options as $option) {
            $schema[] = Select::make('option_values.'.$option->id)
                ->label($option->name)
                ->options($option->values->pluck('value', 'id'));
        }

        return $form->schema($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('optionValues.value')
                    ->badge()
                    ->label('Options'),
                Tables\Columns\TextColumn::make('price')->money('vnd'),
                Tables\Columns\TextColumn::make('quantity'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Model $record, array $data) {
                        if (isset($data['option_values'])) {
                            $record->optionValues()->sync($data['option_values']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Model $record, array $data) {
                        if (isset($data['option_values'])) {
                            $record->optionValues()->sync($data['option_values']);
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
