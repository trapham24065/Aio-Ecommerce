<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Illuminate\Database\Eloquent\Collection;

// #[ApiResource]
class ProductVariant extends Model
{

    use HasFactory;

    #[Groups(['product:read'])]
    protected $fillable
        = [
            'product_id',
            'sku',
            'price',
            'quantity',
        ];

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



