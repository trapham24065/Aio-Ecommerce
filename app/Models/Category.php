<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Notifications\Notification;

class Category extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(static function (Category $category) {
            if ($category->products()->count() > 0) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete a category that has associated products.')
                    ->danger()
                    ->send();
                return false;
            }
            return true;
        });
    }

}
