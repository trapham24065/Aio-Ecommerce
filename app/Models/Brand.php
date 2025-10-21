<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\BrandInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\ApiPlatform\State\BrandProcessor;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            input: BrandInput::class,
            processor: BrandProcessor::class
        ),
        new Put(
            input: BrandInput::class,
            processor: BrandProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
class Brand extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'status'];

    protected $casts
        = [
            'status' => 'boolean',
        ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(
            function (Brand $brand) {
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



