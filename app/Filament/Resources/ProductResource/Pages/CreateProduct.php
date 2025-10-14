<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class CreateProduct extends CreateRecord
{

    protected static string $resource = ProductResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->rule('max:100')
                            ->validationAttribute('Product Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (!$state) {
                                    return;
                                }
                                $sanitizedForSlug = preg_replace('/[^\p{L}\p{N}\s]/u', '', $state);
                                $slug = Str::slug($sanitizedForSlug);
                                $limitedSlug = trim(Str::limit($slug, 60, ''), '-');
                                $set('seo.slug', $limitedSlug);

                                ProductResource::generateSku($get, $set);
                            })
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule->where('category_id', $get('category_id'));
                                }
                            ),

                        Radio::make('type')
                            ->options([
                                Product::TYPE_SIMPLE => 'Simple Product',
                                Product::TYPE_VARIANT => 'Variant Product',
                            ])
                            ->default(Product::TYPE_SIMPLE)
                            ->live()
                            ->required(),
                        Section::make('Pricing & Stock')
                            ->schema([
                                TextInput::make('sku')->required()->unique(ignoreRecord: true)->disabled()->dehydrated(
                                ),
                                TextInput::make('base_cost')->numeric()->prefix('đ')->minValue(1)->required()->maxValue(
                                    9999999999999.99
                                ),

                                Placeholder::make('quantity')
                                    ->label('Total Stock (from Inventory)')
                                    ->content(
                                        fn(?Product $record): string => $record?->total_stock ??
                                            'N/A - Calculated after saving'
                                    ),
                            ])
                            ->visible(fn(Get $get): bool => $get('type') === Product::TYPE_SIMPLE),
                        MarkdownEditor::make('description')
                            ->rule('max:500')
                            ->validationAttribute('Description')
                            ->columnSpanFull(),
                    ])->columnSpan(['lg' => 2]),

                    Section::make('SEO')->relationship('seo')->schema([
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        TextInput::make('meta_title')
                            ->rule('max:100')
                            ->validationAttribute('Meta Title'),
                        TextInput::make('meta_description')
                            ->rule('max:100')
                            ->validationAttribute('Meta Description'),
                    ])->columnSpan(['lg' => 2]),
                ])->columnSpan(['lg' => 2]),
                Group::make()->schema([
                    Section::make('Associations')
                        ->schema([
                            Select::make('category_id')
                                ->label('Category')
                                ->relationship(
                                    name: 'category',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn(Get $get, Set $set) => ProductResource::generateSku($get, $set)),

                            Select::make('brand_id')
                                ->label('Brand')
                                ->relationship(
                                    name: 'brand',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                                )
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('supplier_id')
                                ->label('Supplier')
                                ->relationship(
                                    name: 'supplier',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                                )
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])->columnSpan(['lg' => 1]),
                    Section::make('Images')
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->image()
                                ->directory('products')
                                ->required()
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                                ->helperText('Upload a JPG, PNG, or GIF image. Maximum size 2MB.'),
                        ])->columnSpan(['lg' => 1]),
                    Section::make('Status')
                        ->schema([
                            Toggle::make('status')->label('Active')->default(true)->visible(
                                fn(Get $get): bool => $get('type') === Product::TYPE_SIMPLE
                            ),
                        ])->columnSpan(['lg' => 1]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    protected function getRedirectUrl(): string
    {
        $record = $this->getRecord();

        if ($record->type === Product::TYPE_VARIANT) {
            return self::getResource()::getUrl('edit', ['record' => $record]);
        }

        return self::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['type'] === Product::TYPE_VARIANT) {
            $data['status'] = 0;
        }

        return $data;
    }

}
