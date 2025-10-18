<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Notifications\Notification;
use App\Http\Requests\StoreCategoryRequest;
use App\ApiPlatform\State\CategoryProcessor;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            input: StoreCategoryRequest::class,
            processor: CategoryProcessor::class
        ),
        new Get(),
        new Put(
            input: StoreCategoryRequest::class,
            processor: CategoryProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
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

