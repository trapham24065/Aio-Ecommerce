<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [],
    normalizationContext: ['groups' => ['product:detail:read']]
)]
class VariantValue extends Model
{
    public $timestamps = false;

    protected $table = 'variant_values';

    protected $fillable = [
        'variant_id',
        'option_value_id',
    ];

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class, 'option_value_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
