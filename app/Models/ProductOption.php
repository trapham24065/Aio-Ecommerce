<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Illuminate\Database\Eloquent\Collection;

// #[ApiResource]
class ProductOption extends Model
{

    public $timestamps = false;

    #[Groups(['product:read'])]
    protected $fillable = ['product_id', 'name'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class);
    }

}



