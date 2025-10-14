<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    // CỘT CHÍNH (CHIẾM 2 PHẦN)
                    Group::make()->schema([
                        Section::make('Order Items')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('product_name'),
                                        TextEntry::make('sku'),
                                        TextEntry::make('quantity')->numeric(),
                                        TextEntry::make('price')->money('vnd'),
                                        TextEntry::make('total_price')->money('vnd'),
                                    ])->columns(5),
                            ]),
                        Section::make('Shipping Address')
                            ->relationship('shippingAddress')
                            ->schema([
                                TextEntry::make('full_name'),
                                TextEntry::make('phone'),
                                TextEntry::make('street'),
                                TextEntry::make('city'),
                            ]),
                    ])->columnSpan(2),

                    // CỘT PHỤ (SIDEBAR - CHIẾM 1 PHẦN)
                    Group::make()->schema([
                        Section::make('Order Details')
                            ->schema([
                                TextEntry::make('order_code')
                                    ->label('Order Code')
                                    ->weight('bold'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('customer.full_name'),
                                TextEntry::make('created_at')->dateTime(),
                            ]),
                        Section::make('Financials')
                            ->schema([
                                TextEntry::make('subtotal')->money('vnd'),
                                TextEntry::make('shipping_fee')->money('vnd'),
                                TextEntry::make('discount_amount')->money('vnd'),
                                TextEntry::make('grand_total')->money('vnd')->weight('bold'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

}
