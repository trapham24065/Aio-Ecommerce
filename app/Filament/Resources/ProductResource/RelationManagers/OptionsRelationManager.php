<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\OptionValue;
use Filament\Forms\Components\TextInput;

class OptionsRelationManager extends RelationManager
{

    protected static string $relationship = 'options';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Option Name')
                    ->required()
                    ->maxLength(255),

                TagsInput::make('values')
                    ->label('Option Values')
                    ->placeholder('New value'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Option Name'),

                Tables\Columns\TextColumn::make('values.value')
                    ->label('Option Values')
                    ->badge()
                    ->listWithLineBreaks(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Model $record, array $data) {
                        if (isset($data['values'])) {
                            foreach ($data['values'] as $value) {
                                $record->values()->create(['value' => $value]);
                            }
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['values'] = $record->values->pluck('value')->toArray();
                        return $data;
                    })
                    ->after(function (Model $record, array $data) {
                        if (isset($data['values'])) {
                            $record->values()->delete();
                            foreach ($data['values'] as $value) {
                                $record->values()->create(['value' => $value]);
                            }
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
