<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{

    use HasFactory;

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

}
