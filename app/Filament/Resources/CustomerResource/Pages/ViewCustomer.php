<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{

    protected static string $resource = CustomerResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        Section::make('Order History')
                            ->schema([
                                TextEntry::make('order_history_placeholder')
                                    ->label('')
                                    ->default('Order history will be displayed here in the future.'),
                            ]),
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Customer Details')
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('Full Name')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                TextEntry::make('phone')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                            ]),

                        Section::make('Timestamps')
                            ->schema([
                                TextEntry::make('created_at')->dateTime(),
                                TextEntry::make('updated_at')->since(),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

}
