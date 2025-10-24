<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiProperty;
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
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['supplier:read']]
        ),
        new Post(
            denormalizationContext: ['groups' => ['supplier:write']],
            input: SupplierInput::class,
            processor: SupplierProcessor::class
        ),
        new Get(
            normalizationContext: ['groups' => ['supplier:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['supplier:write']],
            input: SupplierInput::class,
            processor: SupplierProcessor::class
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['supplier:read']],
    denormalizationContext: ['groups' => ['supplier:write']],
    security: "is_granted('ROLE_USER')"
)]
class Supplier extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'home_url', 'status'];

    protected $casts
        = [
            'status' => 'boolean',
        ];

    protected $appends = ['home_url'];

    // ✅ Gắn đúng group 'supplier:read' (có thể kèm receipt:* nếu muốn dùng chung)
    #[Groups(['supplier:read', 'receipt:detail:read', 'receipt:list'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['supplier:read', 'receipt:detail:read', 'receipt:list'])]
    public function getCode()
    {
        return $this->code;
    }

    #[Groups(['supplier:read', 'receipt:detail:read', 'receipt:list'])]
    public function getName()
    {
        return $this->name;
    }

    // Nếu muốn key là snake_case 'home_url', giữ SerializedName như dưới.
    // Nếu chấp nhận camelCase, bỏ SerializedName đi.
    #[Groups(['supplier:read'])]
    #[ApiProperty(readable: true)]
    #[SerializedName('home_url')]
    public function getHomeUrl(): ?string
    {
        return $this->home_url;
    }

    #[Groups(['supplier:read', 'receipt:detail:read', 'receipt:list'])]
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
        });
    }

}
