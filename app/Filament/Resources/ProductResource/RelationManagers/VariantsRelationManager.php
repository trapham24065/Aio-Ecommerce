<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use App\Models\OptionValue;

class VariantsRelationManager extends RelationManager
{

    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form->schema(function (RelationManager $livewire) {
            /** @var Product $product */
            $product = $livewire->getOwnerRecord();

            $skuGenerationClosure = function (Get $get, Set $set) use ($product) {
                $optionValues = $get('option_values'); // Lấy mảng ID của các option value đã chọn

                if (count($optionValues) < $product->options()->count()) {
                    return;
                }

                $selectedValues = OptionValue::find($optionValues)->pluck('value')->toArray();

                $sku = collect([
                    $product->category->code,
                    $product->id,
                ])
                    ->merge($selectedValues)
                    ->map(fn($value) => Str::slug($value))
                    ->join('-');

                $set('sku', strtoupper($sku));
            };

            $schema = [
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('đ'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
            ];

            foreach ($product->options as $option) {
                $schema[] = Select::make('option_values.'.$option->id)
                    ->label($option->name)
                    ->options($option->values->pluck('value', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated($skuGenerationClosure);
            }

            return $schema;
        });
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
