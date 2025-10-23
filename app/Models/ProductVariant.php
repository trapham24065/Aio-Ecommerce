<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Illuminate\Database\Eloquent\Collection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['receipt:list']]),
        new Get(normalizationContext: ['groups' => ['receipt:detail:read']]),
        new Post(),
        new Put(),
        new Delete(),
    ]
)]
class ProductVariant extends Model
{

    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'price', 'quantity'];

    #[Groups(['product:read', 'receipt:detail:read'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['product:read', 'receipt:detail:read'])]
    public function getSku()
    {
        return $this->sku;
    }

    #[Groups(['product:read', 'receipt:detail:read'])]
    public function getPrice()
    {
        return $this->price;
    }

    #[Groups(['product:read', 'receipt:detail:read'])]
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(OptionValue::class, 'variant_values', 'variant_id', 'option_value_id');
    }

    public function getTotalStockAttribute(): int
    {
        return Inventory::where('product_variant_sku', $this->sku)->sum('quantity') ?? 0;
    }

    public function hasStock(): bool
    {
        return Inventory::where('product_variant_sku', $this->sku)->where('quantity', '>', 0)->exists();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id');
    }

}



