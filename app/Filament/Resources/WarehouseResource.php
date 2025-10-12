<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;

class WarehouseResource extends Resource
{

    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Warehouse Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->rule('max:100')
                            ->validationAttribute('Warehouse Name'),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rule('max:50')
                            ->validationAttribute('Code'),
                        Toggle::make('status')
                            ->default(true),
                    ])->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('street')
                            ->label('Street Address')
                            ->rule('max:500')
                            ->validationAttribute('Street Address')
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->rule('max:100')
                            ->validationAttribute('City')
                            ->required(),
                        TextInput::make('state')
                            ->rule('max:100')
                            ->validationAttribute('State')
                            ->label('State / Province'),
                        TextInput::make('postal_code')
                            ->label('Postal Code'),
                        Select::make('country')
                            ->searchable()
                            ->options([
                                'VN' => 'Vietnam',
                                'US' => 'United States',
                                'SG' => 'Singapore',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('code')->searchable(),
                IconColumn::make('status')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Warehouse $record): string => WarehouseResource::getUrl('view', ['record' => $record])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'view'   => Pages\ViewWarehouse::route('/{record}'),
            'edit'   => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }

}
