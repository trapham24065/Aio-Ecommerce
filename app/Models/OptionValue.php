<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
class OptionValue extends Model
{

    public $timestamps = false;

    #[Groups(['product:read'])]
    protected $fillable = ['product_option_id', 'value'];

    #[Groups(['product:read'])]
    public $id;

    #[Groups(['product:read'])]
    public $value;

    #[Groups(['product:read'])]
    public $productOption;

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_values', 'option_value_id', 'variant_id');
    }

}


