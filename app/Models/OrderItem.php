<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{

    public $timestamps = false;

    protected $fillable
        = [
            'order_id',
            'warehouse_id',
            'product_id',
            'product_variant_id',
            'product_name',
            'sku',
            'price',
            'quantity',
            'total_price',
        ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

}
