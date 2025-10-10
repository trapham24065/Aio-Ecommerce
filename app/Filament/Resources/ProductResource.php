<?php

namespace App\Filament\Resources;

use Illuminate\Validation\Rules\Unique;
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
use Illuminate\Validation\Rule;
use Filament\Forms\Get;

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
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $sanitized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $state);

                                    $set('seo.slug', Str::slug($sanitized));
                                }
                            })
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule
                                        ->where('category_id', $get('category_id'));
                                }
                            ),
                        TextInput::make('sku')->required()->unique(ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpanFull(),
                    ])->columns(2),

                    Section::make('SEO')
                        ->relationship('seo')
                        ->schema([
                            TextInput::make('slug')->required()->unique(ignoreRecord: true),
                            TextInput::make('meta_title'),
                            TextInput::make('meta_description'),
                        ]),

                    Section::make('Images')->schema([
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('products')
                            ->required()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->helperText('Upload a JPG, PNG, or GIF image. Maximum size 2MB.'),
                    ]),

                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Pricing & Stock')->schema([
                        TextInput::make('base_cost')->numeric()->prefix('đ')->minValue(1)->required(),
                        TextInput::make('quantity')->numeric()->integer()->default(1)->minValue(1),
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
                            ->required()
                            ->live(),
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
                TextColumn::make('name')->searchable()->limit(50)->tooltip(fn(Product $record): string => $record->name
                ),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('quantity')->sortable(),
                IconColumn::make('status')->boolean()->label('Active'),
            ])
            ->filters([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

}
