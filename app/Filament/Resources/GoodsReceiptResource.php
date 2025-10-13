<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsReceiptResource\Pages;
use App\Filament\Resources\GoodsReceiptResource\RelationManagers;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class GoodsReceiptResource extends Resource
{

    protected static ?string $model = GoodsReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->schema([
                        Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('receipt_date')
                            ->label('Receipt Date')
                            ->default(now())
                            ->required(),

                        Textarea::make('notes')
                            ->rule('max:255')
                            ->validationAttribute('Note')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Items')
                    ->label('Products to Receive')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_variant_sku')
                                    ->label('Product SKU')
                                    ->searchable()
                                    ->options(function () {
                                        $variantsQuery = DB::table('product_variants')
                                            ->select('sku', 'created_at');

                                        return DB::table('products')
                                            ->where('type', Product::TYPE_SIMPLE)
                                            ->select('sku', 'created_at')
                                            ->union($variantsQuery)
                                            ->orderBy('created_at', 'desc')
                                            ->limit(10)
                                            ->pluck('sku', 'sku');
                                    })
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->required()
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receipt_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                    'completed' => 'success',
                    'cancelled' => 'danger',
                }),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->url(
                            fn(GoodsReceipt $record): string => GoodsReceiptResource::getUrl(
                                'view',
                                ['record' => $record]
                            )
                        ),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel Receipt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(GoodsReceipt $record): bool => $record->status !== 'cancelled')
                        ->action(function (GoodsReceipt $record) {
                            DB::transaction(function () use ($record) {
                                foreach ($record->items as $item) {
                                    $inventory = Inventory::where('warehouse_id', $record->warehouse_id)
                                        ->where('product_variant_sku', $item->product_variant_sku)
                                        ->first();
                                    if ($inventory) {
                                        $inventory->quantity -= $item->quantity;
                                        $inventory->save();
                                    }

                                    InventoryTransaction::create([
                                        'warehouse_id'        => $record->warehouse_id,
                                        'product_variant_sku' => $item->product_variant_sku,
                                        'type'                => 'IN_REVERSED',
                                        'quantity_change'     => -$item->quantity,
                                        'reference_id'        => $record->id,
                                        'reference_type'      => GoodsReceipt::class,
                                        'user_id'             => auth()->id(),
                                        'notes'               => 'Reversal for receipt: '.$record->code,
                                    ]);
                                }

                                $record->status = 'cancelled';
                                $record->save();
                            });

                            Notification::make()->title('Goods receipt has been cancelled successfully!')->success()
                                ->send();
                        }),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGoodsReceipts::route('/'),
            'create' => Pages\CreateGoodsReceipt::route('/create'),
            'view'   => Pages\ViewGoodsReceipt::route('/{record}'),
            'edit'   => Pages\EditGoodsReceipt::route('/{record}/edit'),
        ];
    }

}
