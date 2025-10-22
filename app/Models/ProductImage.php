<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
class ProductImage extends Model
{

    use HasFactory;

    public $timestamps = false;

    #[Groups(['product:read'])]
    protected $fillable
        = [
            'product_id',
            'product_variant_id',
            'url',
            'alt_text',
            'position',
        ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

}

