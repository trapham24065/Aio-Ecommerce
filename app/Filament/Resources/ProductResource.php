<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Get;
use Filament\Forms\Components\Radio;

class ProductResource extends Resource
{

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail'),

                TextColumn::make('name')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(Product $record): string => $record->name),

                BadgeColumn::make('type')
                    ->colors([
                        'primary' => Product::TYPE_SIMPLE,
                        'success' => Product::TYPE_VARIANT,
                    ])
                    ->formatStateUsing(fn(string $state): string => Str::title($state)),

                TextColumn::make('sku')
                    ->label('SKU / Variants')
                    ->searchable()
                    ->getStateUsing(function (Product $record): string {
                        if ($record->type === Product::TYPE_SIMPLE) {
                            return $record->sku ?? '-';
                        }
                        $variantCount = $record->variants()->count();
                        return "{$variantCount} ".Str::plural('Variant', $variantCount);
                    }),

                TextColumn::make('category.name')->sortable(),

                TextColumn::make('quantity')
                    ->label('Total Stock')
                    ->sortable()
                    ->getStateUsing(function (Product $record): string {
                        $quantity = $record->quantity ?? 0;
                        if ($record->type === Product::TYPE_VARIANT) {
                            $variantCount = $record->variants()->count();
                            return "{$quantity} (in {$variantCount} variants)";
                        }
                        return (string)$quantity;
                    }),

                IconColumn::make('status')->boolean()->label('Active'),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products')
                    ->native(false),

                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 5))
                    ->indicator('Low Stock'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OptionsRelationManager::class,
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function generateSku(Get $get, Set $set): void
    {
        $name = $get('name');
        $categoryCode = Category::find($get('category_id'))?->code;

        if ($name && $categoryCode) {
            $productSlugPart = Str::slug($name);
            $limitedProductSlugPart = trim(Str::limit($productSlugPart, 40, ''), '-');
            $sku = Str::upper($categoryCode.'-'.$limitedProductSlugPart);
            $set('sku', $sku);
        }
    }

}
