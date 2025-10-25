<?php

namespace App\Models;

use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiPlatform\State\GoodsReceiptProvider;
use App\Http\Requests\StoreWarehouseRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ApiPlatform\Metadata\Get;
use App\ApiPlatform\State\WarehouseProcessor;
use App\Dto\WarehouseInput;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['warehouse:list', 'receipt:detail:read']],
        ),
        new Post(
            input: WarehouseInput::class,
            processor: WarehouseProcessor::class
        ),
        new Get(normalizationContext: ['groups' => ['warehouse:detail:read', 'receipt:detail:read']],),
        new Put(
            input: WarehouseInput::class,
            processor: WarehouseProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
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

    protected $casts
        = [
            'status' => 'boolean',
        ];

    protected $attributes = ['status' => true];

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:list', 'warehouse:detail:read'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:list', 'warehouse:detail:read'])]
    public function getName()
    {
        return $this->name;
    }

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:list', 'warehouse:detail:read'])]
    public function getCode()
    {
        return $this->code;
    }

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:detail:read'])]
    public function getCity()
    {
        return $this->city;
    }

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:detail:read'])]
    public function getCountry()
    {
        return $this->country;
    }

    #[Groups(['receipt:detail:read', 'receipt:list', 'warehouse:detail:read'])]
    public function getStatus()
    {
        return $this->status;
    }

    #[Groups(['receipt:detail:read', 'warehouse:detail:read'])]
    public function getInventory()
    {
        return $this->inventory()->get();
    }

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

