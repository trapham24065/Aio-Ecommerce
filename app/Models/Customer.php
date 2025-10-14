<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Notifications\Notification;

class Customer extends Model
{

    use HasFactory;

    protected $fillable
        = [
            'first_name',
            'last_name',
            'email',
            'phone',
        ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Customer $customer) {
            if ($customer->orders()->exists()) {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('Cannot delete a customer who has placed orders.')
                    ->danger()
                    ->send();

                return false;
            }
        });
    }

}
