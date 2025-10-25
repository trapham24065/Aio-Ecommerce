<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Http\Requests\StoreInventoryRequest;
use App\ApiPlatform\State\InventoryProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['warehouse:list']]),
        new Post(
            input: StoreInventoryRequest::class,
            processor: InventoryProcessor::class
        ),
        new Get(),
        new Put(
            input: StoreInventoryRequest::class,
            processor: InventoryProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
class Inventory extends Model
{

    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = ['warehouse_id', 'product_variant_sku', 'quantity'];

    #[Groups(['warehouse:detail:read', 'warehouse:list'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['warehouse:detail:read', 'warehouse:list'])]
    #[SerializedName('product_variant_sku')]
    public function getProductVariantSku(): ?string
    {
        return $this->product_variant_sku;
    }

    #[Groups(['warehouse:detail:read', 'warehouse:list'])]
    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_sku', 'sku');
    }

}

