<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Brand $brand) {
            if ($brand->products()->exists()) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete a brand that has associated products.')
                    ->danger()
                    ->send();

                return false;
            }
            return true;
        }
        );
    }

}
