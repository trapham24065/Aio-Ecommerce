<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;

class GoodsReceiptItem extends Model
{

    use HasFactory;

    public $timestamps = false;

    protected $fillable
        = [
            'goods_receipt_id',
            'product_variant_sku',
            'quantity',
        ];

    #[Groups(['receipt:detail:read', 'receipt:read', 'receipt:list'])]
    public $id;

    #[Groups(['receipt:detail:read', 'receipt:write', 'receipt:read', 'receipt:list'])]
    public string $product_variant_sku;

    #[Groups(['receipt:detail:read', 'receipt:write', 'receipt:read', 'receipt:list'])]
    public int $quantity;

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_sku', 'sku');
    }

}

