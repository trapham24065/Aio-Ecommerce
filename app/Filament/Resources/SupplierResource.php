<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Brand;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class SupplierResource extends Resource
{

    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->rule('max:100')
                    ->unique(ignoreRecord: true)
                    ->validationAttribute('Supplier Name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) use ($form) {
                        if (!$state) {
                            return;
                        }
                        $sanitized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $state);
                        $baseCode = Str::slug($sanitized);
                        $finalCode = $baseCode;
                        $counter = 1;
                        $recordId = $form->getRecord()?->id;
                        while (
                        Supplier::where('code', $finalCode)
                            ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                            ->exists()
                        ) {
                            $counter++;
                            $finalCode = $baseCode.'-'.$counter;
                        }

                        $set('code', $finalCode);
                    }),

                TextInput::make('code')
                    ->required()
                    ->rule('max:100')
                    ->validationAttribute('Supplier Code')
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('home_url')
                    ->label('Website URL')
                    ->url()
                    ->rule('max:100')
                    ->validationAttribute('Website URL'),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('home_url')->searchable(),
                IconColumn::make('status')->boolean()->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->url(fn(Supplier $record): string => SupplierResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view'   => Pages\ViewSupplier::route('/{record}'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

}
