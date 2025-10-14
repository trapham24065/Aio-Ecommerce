<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\OptionsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Actions\Action;

class EditProduct extends EditRecord
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
                            ->disabled(fn(Product $record): bool => $record->hasStock())
                            ->helperText(
                                fn(Product $record): string => $record->hasStock()
                                    ? 'Cannot be changed when the product has stock.' : ''
                            )
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (!$state) {
                                    return;
                                }
                                $set('seo.slug', Str::slug($state));
                                ProductResource::generateSku($get, $set);
                            })
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where(
                                'category_id',
                                $get('category_id')
                            )),

                        Radio::make('type')->options([
                            Product::TYPE_SIMPLE => 'Simple Product',
                            Product::TYPE_VARIANT => 'Variant Product',
                        ])
                            ->live()
                            ->required()
                            ->disabled(fn(Product $record): bool => $record->hasStock())
                            ->helperText('Product type cannot be changed after it has stock in inventory.'),

                        MarkdownEditor::make('description')
                            ->rule('max:500')
                            ->validationAttribute('Description')->columnSpanFull(),
                    ])->columns(2),

                    Section::make('Pricing & Stock')
                        ->schema([
                            TextInput::make('sku')->required()->unique(ignoreRecord: true)->disabled()->dehydrated(),
                            TextInput::make('base_cost')->numeric()->prefix('đ')->minValue(1)->required()->maxValue(
                                9999999999999.99
                            ),
                            Placeholder::make('quantity')
                                ->label('Total Stock (from Inventory)')
                                ->content(fn(?Product $record): int => $record?->total_stock ?? 0),
                        ])
                        ->visible(fn(Get $get): bool => $get('type') === Product::TYPE_SIMPLE),

                    Section::make('SEO')->relationship('seo')->schema([
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        TextInput::make('meta_title')
                            ->rule('max:100')
                            ->validationAttribute('Meta Title'),
                        TextInput::make('meta_description')
                            ->rule('max:100')
                            ->validationAttribute('Meta Description'),
                    ]),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Associations')->schema([
                        Select::make('category_id')->label('Category')->relationship(
                            name: 'category',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                        )->searchable()
                            ->preload()->required()->live()->afterStateUpdated(
                                fn(Get $get, Set $set) => ProductResource::generateSku($get, $set)
                            )
                            ->disabled(fn(Product $record): bool => $record->hasStock()),
                        Select::make('brand_id')->label('Brand')->relationship(
                            name: 'brand',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                        )->searchable()->preload()
                            ->required(),
                        Select::make('supplier_id')->label('Supplier')->relationship(
                            name: 'supplier',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->where('status', 1)
                        )->searchable()
                            ->preload()->required(),
                    ]),
                    Section::make('Images')->schema([
                        FileUpload::make('thumbnail')->image()->directory('products')->required(),
                    ]),
                    Toggle::make('status')->label('Active')->default(true)
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if ($get('type') === Product::TYPE_VARIANT && $value === true) {
                                        $product = $this->getRecord();
                                        if ($product->variants()->count() === 0) {
                                            $fail(
                                                'You cannot activate a variant product without adding at least one variant.'
                                            );
                                        }
                                    }
                                };
                            },
                        ]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public function getRelationManagers(): array
    {
        if ($this->record->type === Product::TYPE_VARIANT) {
            return static::getResource()::getRelations();
        }

        return [];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->requiresConfirmation(
                fn(array $data): bool => $this->getRecord()->type !== $data['type']
            )
            ->modalHeading('Confirm Product Type Change')
            ->modalDescription(
                'Changing the product type will delete all associated variants and options. Are you sure you want to proceed? This action cannot be undone.'
            )
            ->modalSubmitActionLabel('Yes, change it');
    }

    protected function afterSave(): void
    {
        $product = $this->getRecord();

        if ($product->wasChanged('type')) {
            Notification::make()
                ->title('Product Type Changed')
                ->body('The page has been reloaded to reflect the new product type.')
                ->success()
                ->send();

            $this->redirect(self::getResource()::getUrl('edit', ['record' => $product]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }

}
