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
    ]
)]
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

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getQuantity()
    {
        return $this->quantity;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    #[SerializedName('product_variant_sku')]
    public function getProductVariantSku(): ?string
    {
        return $this->product_variant_sku;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
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
