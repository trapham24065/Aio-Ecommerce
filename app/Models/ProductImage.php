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

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['product:detail:read']],),
        new Get(normalizationContext: ['groups' => ['product:detail:read']],),
        new Post(),
        new Put(),
        new Delete(),
    ]

)]
class ProductImage extends Model
{

    use HasFactory;

    public $timestamps = false;

    protected $fillable
        = [
            'product_id',
            'product_variant_id',
            'url',
            'alt_text',
            'position',
        ];

    #[Groups(['product:detail:read'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['product:detail:read'])]
    public function getUrl()
    {
        return $this->url;
    }

    #[Groups(['product:detail:read'])]
    public function getAltText()
    {
        return $this->alt_text;
    }

    #[Groups(['product:detail:read'])]
    public function getPosition()
    {
        return $this->position;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

}
