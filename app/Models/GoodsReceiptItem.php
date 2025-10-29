<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['receipt:list']]),
        new Get(normalizationContext: ['groups' => ['receipt:detail:read']]),
        new Post(),
        new Put(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['items:read', 'items:detail:read', 'receipt:list', 'receipt:detail:read']],
)]
class GoodsReceiptItem extends Model
{

    use HasFactory;

    protected $table = 'goods_receipt_items';

    public $timestamps = false;

    protected $fillable
        = [
            'goods_receipt_id',
            'product_variant_sku',
            'quantity',
        ];

    protected $visible = ['id', 'product_variant_sku', 'quantity'];

    protected $with = ['productVariant'];

    #[Groups(['receipt:detail:read', 'receipt:list', 'items:read', 'items:detail:read'])]
    protected $id;

    #[Groups(['receipt:detail:read', 'receipt:list', 'items:read', 'items:detail:read'])]
    protected $quantity;

    #[Groups(['receipt:detail:read', 'receipt:list', 'items:read', 'items:detail:read'])]
    protected $product_variant_sku;

    #[Groups(['receipt:detail:read', 'items:read', 'items:detail:read'])]
    #[SerializedName('product_variant')]
    public function getProductVariant()
    {
        return $this->productVariant;
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_sku', 'sku');
    }

}
