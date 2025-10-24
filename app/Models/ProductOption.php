<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Illuminate\Database\Eloquent\Collection;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['receipt:list', 'product:detail:read']],),
        new Get(normalizationContext: ['groups' => ['receipt:detail:read', 'product:detail:read']],),
        new Post(),
        new Put(),
        new Delete(),
    ]

)]
class ProductOption extends Model
{

    public $timestamps = false;

    #[Groups(['product:read'])]
    protected $fillable = ['product_id', 'name'];

    #[Groups(['product:detail:read'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['product:detail:read'])]
    public function getName()
    {
        return $this->name;
    }

    #[Groups(['product:detail:read'])]
    public function getValues()
    {
        return $this->values()->get();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class);
    }

}



