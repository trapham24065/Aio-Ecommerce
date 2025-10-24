<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [],
    normalizationContext: ['groups' => ['product:detail:read', 'product:read']]
)]
class OptionValue extends Model
{

    public $timestamps = false;

    protected $fillable = ['product_option_id', 'value'];

    #[Groups(['product:detail:read', 'product:read'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['product:detail:read', 'product:read'])]
    public function getValue()
    {
        return $this->value;
    }

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_values', 'option_value_id', 'variant_id');
    }

}


