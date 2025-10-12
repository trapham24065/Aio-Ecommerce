<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{

    use HasFactory;

    protected $fillable
        = [
            'warehouse_id',
            'product_variant_sku',
            'type',
            'quantity_change',
            'notes',
            'user_id',
            'reference_id',
            'reference_type',
        ];

    public const UPDATED_AT = null;

}
