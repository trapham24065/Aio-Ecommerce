<?php

namespace App\Models;

use ApiPlatform\Metadata\Patch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\ProductInput;
use App\ApiPlatform\State\ProductProcessor;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            input: ProductInput::class,
            processor: ProductProcessor::class
        ),
        new Get(),
        new Put(
            input: ProductInput::class,
            processor: ProductProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
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

    protected $casts
        = [
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
        static::creating(static function ($product) {
            if (is_string($product->category) && str_starts_with($product->category, '/api/categories/')) {
                $product->category_id = basename($product->category);
            }

            if (is_string($product->supplier) && str_starts_with($product->supplier, '/api/suppliers/')) {
                $product->supplier_id = basename($product->supplier);
            }

            if (is_string($product->brand) && str_starts_with($product->brand, '/api/brands/')) {
                $product->brand_id = basename($product->brand);
            }
        });
        static::updating(static function (Product $product) {
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


