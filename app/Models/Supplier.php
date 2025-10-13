<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'home_url', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Supplier $supplier) {
            if ($supplier->products()->exists()) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete a supplier that has associated products.')
                    ->danger()
                    ->send();

                return false;
            }
            return true;
        }
        );
    }

}
