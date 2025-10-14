<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    public $timestamps = false;

    protected $fillable
        = [
            'order_id',
            'product_id',
            'product_variant_id',
            'product_name',
            'sku',
            'price',
            'quantity',
            'total_price',
        ];

}
