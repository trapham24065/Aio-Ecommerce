<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

class ProductResource extends Resource
{

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('sku')->required()->unique(ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpanFull(),
                    ])->columns(2),

                    Section::make('Images')->schema([
                        FileUpload::make('thumbnail')->image()->directory('products'),
                    ]),

                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Pricing & Stock')->schema([
                        TextInput::make('base_cost')->numeric()->prefix('đ'),
                        TextInput::make('quantity')->numeric()->default(1),
                    ]),

                    Section::make('Associations')->schema([
                        Select::make('category_id')
                            ->searchable()
                            ->options(fn() => Category::limit(15)->pluck('name', 'id'))
                            ->getSearchResultsUsing(function (string $search): array {
                                return Category::where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return Category::find($value)?->name;
                            })
                            ->required(),
                        Select::make('brand_id')
                            ->searchable()
                            ->options(fn() => Brand::limit(15)->pluck('name', 'id'))
                            ->getSearchResultsUsing(function (string $search): array {
                                return Brand::where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return Brand::find($value)?->name;
                            })
                            ->required(),

                        Select::make('supplier_id')
                            ->searchable()
                            ->options(fn() => Supplier::limit(15)->pluck('name', 'id'))
                            ->getSearchResultsUsing(function (string $search): array {
                                return Supplier::where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return Supplier::find($value)?->name;
                            })
                            ->required(),
                    ]),

                    Section::make('Status')->schema([
                        Toggle::make('status')->label('Active')->default(true),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('quantity')->sortable(),
                IconColumn::make('status')->boolean()->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

}
