<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
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

class CreateOrder extends CreateRecord
{

    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer & Order Details')->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'full_name')
                        ->searchable()->preload()->required(),
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
                                ->reactive() // Rất quan trọng!
                                ->getSearchResultsUsing(function (string $search) {
                                    $variantSkus = ProductVariant::where('sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('sku', 'sku');
                                    $simpleSkus = Product::where('type', Product::TYPE_SIMPLE)
                                        ->where('sku', 'like', "{$search}%")
                                        ->limit(25)
                                        ->pluck('sku', 'sku');
                                    return $variantSkus->merge($simpleSkus);
                                })
                                // Đặt afterStateUpdated ở đây, nó sẽ được gọi trên Select
                                ->afterStateUpdated(function ($state, Set $set) {
                                    // Tự động điền giá
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
                                ->required(),

                            TextInput::make('quantity')
                                ->numeric()->minValue(1)->default(1)
                                ->reactive() // Rất quan trọng!
                                ->required(),

                            // Bỏ disabled(), vì chúng ta cần tính toán và set giá trị cho nó
                            TextInput::make('price')->numeric()->required(),

                            // SỬA LẠI TRƯỜNG NÀY
                            Placeholder::make('total_price')
                                ->label('Total')
                                ->content(function (Get $get): string {
                                    $price = (float)($get('price') ?? 0);
                                    $quantity = (int)($get('quantity') ?? 0);
                                    return number_format($price * $quantity).' VND';
                                }),
                        ])
                        // DI CHUYỂN LOGIC TÍNH TỔNG VÀO ĐÂY
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Get $get): array {
                            $price = (float)($data['price'] ?? 0);
                            $quantity = (int)($data['quantity'] ?? 0);
                            $data['total_price'] = $price * $quantity;

                            // Lấy tên sản phẩm để lưu lại
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
                            // Tự động cập nhật tổng tiền của cả đơn hàng
                            self::updateTotals($get, $set);
                        })
                        ->columns(4)
                        ->required(),
                ]),

                Section::make('Shipping Address')->schema([
                    TextInput::make('shippingAddress.full_name')->label('Full Name')->required(),
                    TextInput::make('shippingAddress.phone')->label('Phone')->required(),
                    TextInput::make('shippingAddress.street')->label('Street')->required(),
                    TextInput::make('shippingAddress.city')->label('City')->required(),
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

}
