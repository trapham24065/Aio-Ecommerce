<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BrandResource extends Resource
{

    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rule('max:100')
                    ->validationAttribute('Brand Name')
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
                        Brand::where('code', $finalCode)
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
                    ->validationAttribute('Brand Code')
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(),

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
                        ->url(fn(Brand $record): string => BrandResource::getUrl('view', ['record' => $record])),
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
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }

}
