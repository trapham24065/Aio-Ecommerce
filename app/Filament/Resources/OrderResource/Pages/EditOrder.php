<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;

class EditOrder extends EditRecord
{

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Order Summary')
                            ->schema([
                                Placeholder::make('order_code')
                                    ->label('Order Code')
                                    ->content(fn(?Order $record): string => $record?->order_code ?? '-'),

                                Placeholder::make('customer_name')
                                    ->label('Customer')
                                    ->content(fn(?Order $record): string => $record?->customer?->full_name ?? '-'),

                                Placeholder::make('created_at')
                                    ->label('Created On')
                                    ->content(
                                        fn(?Order $record): string => $record?->created_at?->toFormattedDateString() ??
                                            '-'
                                    ),
                            ])->columns(3),

                        Section::make('Financials')
                            ->schema([
                                Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(
                                        fn(?Order $record): string => number_format($record?->subtotal ?? 0).' VND'
                                    ),

                                Placeholder::make('shipping_fee')
                                    ->label('Shipping Fee')
                                    ->content(
                                        fn(?Order $record): string => number_format($record?->shipping_fee ?? 0).' VND'
                                    ),

                                Placeholder::make('discount_amount')
                                    ->label('Discount')
                                    ->content(
                                        fn(?Order $record): string => number_format($record?->discount_amount ?? 0)
                                            .' VND'
                                    ),

                                Placeholder::make('grand_total')
                                    ->label('Grand Total')
                                    ->content(
                                        fn(?Order $record): string => number_format($record?->grand_total ?? 0).' VND'
                                    ),
                            ])->columns(4),
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Order Actions')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'pending'    => 'Pending',
                                        'processing' => 'Processing',
                                        'shipped'    => 'Shipped',
                                        'delivered'  => 'Delivered',
                                        'cancelled'  => 'Cancelled',
                                    ])->required(),
                            ]),

                        Section::make('Shipping Address')
                            ->relationship('shippingAddress')
                            ->schema([
                                TextInput::make('full_name')->required(),
                                TextInput::make('phone')->tel()->required(),
                                TextInput::make('street')->required()->columnSpanFull(),
                                TextInput::make('ward')->label('Ward / Commune'),
                                TextInput::make('district')->required(),
                                TextInput::make('city')->required(),
                                Select::make('country')
                                    ->searchable()
                                    ->options([
                                        'VN' => 'Vietnam',
                                        'US' => 'United States',
                                        'SG' => 'Singapore',
                                    ])
                                    ->required(),
                            ])
                            ->disabled(
                                fn(?Order $record) => in_array($record?->status, ['shipped', 'delivered', 'cancelled'])
                            ),
                    ])->columnSpan(1),
                ]),
            ]);
    }
    
    protected function afterSave(): void
    {
        $addressData = $this->form->getState()['shippingAddress'];

        if ($addressData) {
            $addressData['type'] = 'shipping';

            $order = $this->getRecord();

            $order->shippingAddress()->updateOrCreate(
                ['order_id' => $order->id, 'type' => 'shipping'],
                $addressData
            );
        }
    }

}
