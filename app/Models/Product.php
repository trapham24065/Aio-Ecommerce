<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{

    use HasFactory, SoftDeletes;

    public const TYPE_SIMPLE = 'simple';

    public const TYPE_VARIANT = 'variant';

    protected $fillable
        = [
            'type',
            'category_id',
            'supplier_id',
            'brand_id',
            'name',
            'sku',
            'description',
            'thumbnail',
            'base_cost',
            'quantity',
            'flag',
            'status',
        ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->type === self::TYPE_SIMPLE) {
            return Inventory::where('product_variant_sku', $this->sku)->sum('quantity') ?? 0;
        }

        $variantSkus = $this->variants()->pluck('sku');
        return Inventory::whereIn('product_variant_sku', $variantSkus)->sum('quantity') ?? 0;
    }

    public function hasStock(): bool
    {
        if ($this->type === self::TYPE_SIMPLE && $this->sku) {
            return Inventory::where('product_variant_sku', $this->sku)->where('quantity', '>', 0)->exists();
        }

        if ($this->type === self::TYPE_VARIANT) {
            $variantSkus = $this->variants()->pluck('sku');
            if ($variantSkus->isEmpty()) {
                return false;
            }
            return Inventory::whereIn('product_variant_sku', $variantSkus)->where('quantity', '>', 0)->exists();
        }

        return false;
    }

    protected static function booted(): void
    {
        static::updating(function (Product $product) {
            if ($product->isDirty('type')) {
                $originalType = $product->getOriginal('type');

                if ($originalType === self::TYPE_VARIANT) {
                    $product->options()->delete();
                    $product->variants()->delete();
                    $product->images()->delete();
                }

                if ($originalType === self::TYPE_SIMPLE) {
                    $product->sku = null;
                    $product->base_cost = null;
                    $product->quantity = 0;
                }
            }
        });
    }

}
