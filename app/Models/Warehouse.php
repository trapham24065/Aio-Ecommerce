<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{

    use HasFactory;

    protected $fillable
        = [
            'name',
            'code',
            'street',
            'city',
            'state',
            'postal_code',
            'country',
            'status',
        ];

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    protected static function booted(): void
    {
        static::deleting(static function (Warehouse $warehouse) {
            if ($warehouse->inventory()->where('quantity', '>', 0)->exists()) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete a warehouse that still has stock. Please transfer or adjust the stock first.')
                    ->danger()
                    ->send();

                return false;
            }

            if (Warehouse::count() === 1) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete the last remaining warehouse.')
                    ->danger()
                    ->send();

                return false;
            }
            return true;
        });
    }

}
