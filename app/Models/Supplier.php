<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Dto\SupplierInput;
use App\ApiPlatform\State\SupplierProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            input: SupplierInput::class,
            processor: SupplierProcessor::class
        ),
        new Get(),
        new Put(
            input: SupplierInput::class,
            processor: SupplierProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
class Supplier extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'home_url', 'status'];

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getName()
    {
        return $this->name;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getHomeUrl()
    {
        return $this->home_url;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getStatus()
    {
        return $this->status;
    }

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


