<?php

namespace App\Filament\Resources\GoodsReceiptResource\Pages;

use App\Filament\Resources\GoodsReceiptResource;
use App\Models\GoodsReceipt;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsReceipt extends ViewRecord
{

    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array

    {
        return [
            Actions\Action::make('print')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn(): string => route('goods-receipt.print', ['receipt' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Received Items')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('product_variant_sku')
                                            ->label('Product SKU'),
                                        TextEntry::make('quantity')
                                            ->numeric(),
                                    ])
                                    ->columns(2),
                            ]),
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Details')
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Receipt Code')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('receipt_date')
                                    ->date(),

                                TextEntry::make('warehouse.name')
                                    ->label('Warehouse')
                                    ->icon('heroicon-o-home-modern'),

                                TextEntry::make('supplier.name')
                                    ->label('Supplier')
                                    ->icon('heroicon-o-truck'),
                            ]),

                        Section::make('Analytics')
                            ->schema([
                                TextEntry::make('total_quantity')
                                    ->label('Total Quantity Received')
                                    ->icon('heroicon-o-inbox-stack')
                                    ->getStateUsing(fn(GoodsReceipt $record): int => $record->items()->sum('quantity')),

                                TextEntry::make('total_items')
                                    ->label('Total Unique Items')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->getStateUsing(fn(GoodsReceipt $record): int => $record->items()->count()),
                            ]),

                        Section::make('Notes')
                            ->schema([
                                TextEntry::make('notes')
                                    ->hiddenLabel()
                                    ->markdown(),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

}
