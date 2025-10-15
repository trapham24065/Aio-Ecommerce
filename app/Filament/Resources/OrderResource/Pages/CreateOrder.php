<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CreateOrder extends CreateRecord
{

    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer & Order Details')->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->first_name.' '.$record->last_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (is_null($state)) {
                                return;
                            }
                            $customer = Customer::find($state);
                            if ($customer) {
                                $set('shippingAddress.full_name', $customer->full_name);
                                $set('shippingAddress.phone', $customer->phone);
                            }
                        }),
                    Select::make('status')
                        ->options([
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'delivered'  => 'Delivered',
                            'cancelled'  => 'Cancelled',
                        ])->default('pending')->required(),
                    Textarea::make('notes')->columnSpanFull(),
                ])->columns(2),

                Section::make('Order Items')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_variant_sku')
                                ->label('Product SKU')
                                ->searchable()
                                ->reactive()
                                ->getSearchResultsUsing(function (string $search) {
                                    if (empty($search)) {
                                        return [];
                                    }

                                    $variantSkus = ProductVariant::query()
                                        ->join(
                                            'inventory',
                                            'product_variants.sku',
                                            '=',
                                            'inventory.product_variant_sku'
                                        )
                                        ->where('inventory.quantity', '>', 0)
                                        ->where('product_variants.sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('product_variants.sku', 'product_variants.sku');

                                    $simpleSkus = Product::query()
                                        ->join('inventory', 'products.sku', '=', 'inventory.product_variant_sku')
                                        ->where('inventory.quantity', '>', 0)
                                        ->where('products.type', Product::TYPE_SIMPLE)
                                        ->where('products.sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('products.sku', 'products.sku');

                                    return $variantSkus->merge($simpleSkus);
                                })
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $variant = ProductVariant::where('sku', $state)->first();
                                    if ($variant) {
                                        $set('price', $variant->price);
                                    } else {
                                        $product = Product::where('sku', 'state')->first();
                                        if ($product) {
                                            $set('price', $product->base_cost);
                                        }
                                    }
                                })
                                ->required()
                                ->columnSpan(2),
                            Select::make('warehouse_id')
                                ->label('Warehouse (Stock)')
                                ->reactive()
                                ->options(function (Get $get) {
                                    $sku = $get('product_variant_sku');
                                    if (!$sku) {
                                        return [];
                                    }

                                    return Inventory::where('product_variant_sku', $sku)
                                        ->where('quantity', '>', 0)
                                        ->join('warehouses', 'inventory.warehouse_id', '=', 'warehouses.id')
                                        ->get(['inventory.warehouse_id', 'warehouses.name', 'inventory.quantity'])
                                        ->mapWithKeys(function ($item) {
                                            return [$item->warehouse_id => "{$item->name} (Stock: {$item->quantity})"];
                                        });
                                })
                                ->visible(fn(Get $get) => filled($get('product_variant_sku')))
                                ->required(),

                            TextInput::make('quantity')
                                ->numeric()->minValue(1)->default(1)
                                ->reactive()
                                ->required()
                                ->rules([
                                    function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $sku = $get('product_variant_sku');
                                            $warehouseId = $get('warehouse_id');
                                            if (!$sku || !$warehouseId) {
                                                return;
                                            }

                                            $stock = Inventory::where('warehouse_id', $warehouseId)
                                                ->where('product_variant_sku', $sku)
                                                ->value('quantity');

                                            if ($value > $stock) {
                                                $fail("The quantity cannot exceed the available stock of {$stock}.");
                                            }
                                        };
                                    },
                                ]),

                            TextInput::make('price')->numeric()->required(),

                            Placeholder::make('total_price')
                                ->label('Total')
                                ->content(function (Get $get): string {
                                    $price = (float)($get('price') ?? 0);
                                    $quantity = (int)($get('quantity') ?? 0);
                                    return number_format($price * $quantity).' VND';
                                }),
                        ])
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Get $get): array {
                            $price = (float)($data['price'] ?? 0);
                            $quantity = (int)($data['quantity'] ?? 0);
                            $data['total_price'] = $price * $quantity;

                            $variant = ProductVariant::where('sku', $data['product_variant_sku'])->first();
                            if ($variant) {
                                $data['product_name'] = $variant->product->name;
                                $data['product_id'] = $variant->product_id;
                                $data['product_variant_id'] = $variant->id;
                                $data['sku'] = $variant->sku;
                            } else {
                                $product = Product::where('sku', $data['product_variant_sku'])->first();
                                if ($product) {
                                    $data['product_name'] = $product->name;
                                    $data['product_id'] = $product->id;
                                    $data['sku'] = $product->sku;
                                }
                            }

                            return $data;
                        })
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        })
                        ->columns(4)
                        ->required(),
                ]),

                Section::make('Shipping Address')
                    ->schema([
                        TextInput::make('shippingAddress.full_name')
                            ->label('Full Name')
                            ->required(),
                        TextInput::make('shippingAddress.phone')
                            ->label('Phone')
                            ->tel()
                            ->required(),
                        TextInput::make('shippingAddress.street')
                            ->label('Street Address')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('shippingAddress.ward')
                            ->label('Ward / Commune'),
                        TextInput::make('shippingAddress.district')
                            ->label('District / County')
                            ->required(),
                        TextInput::make('shippingAddress.city')
                            ->label('City / Province')
                            ->required(),
                        Select::make('shippingAddress.country')
                            ->label('Country')
                            ->searchable()
                            ->options([
                                'VN' => 'Vietnam',
                                'US' => 'United States',
                                'SG' => 'Singapore',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Summary')->schema([
                    TextInput::make('shipping_fee')->numeric()->default(0)->reactive()->afterStateUpdated(
                        fn(Get $get, Set $set) => self::updateTotals($get, $set)
                    ),
                    TextInput::make('discount_amount')->numeric()->default(0)->reactive()->afterStateUpdated(
                        fn(Get $get, Set $set) => self::updateTotals($get, $set)
                    ),
                    TextInput::make('grand_total')->numeric()->disabled(),
                ])->columns(3),
            ]);
    }

    // Hàm tính toán tổng tiền
    public static function updateTotals(Get $get, Set $set): void
    {
        $subtotal = 0;
        foreach ($get('items') as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping_fee = (float)$get('shipping_fee');
        $discount_amount = (float)$get('discount_amount');
        $grand_total = $subtotal + $shipping_fee - $discount_amount;

        $set('subtotal', $subtotal);
        $set('grand_total', $grand_total);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $subtotal = 0;
        if (isset($this->data['items'])) {
            foreach ($this->data['items'] as $item) {
                $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            }
        }

        $shipping_fee = (float)($this->data['shipping_fee'] ?? 0);
        $discount_amount = (float)($this->data['discount_amount'] ?? 0);

        $data['subtotal'] = $subtotal;
        $data['grand_total'] = $subtotal + $shipping_fee - $discount_amount;

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->getRecord();
        $shippingAddressData = $this->form->getState()['shippingAddress'];

        if ($shippingAddressData) {
            $shippingAddressData['type'] = 'shipping';
            $order->shippingAddress()->create($shippingAddressData);
        }
    }

}
