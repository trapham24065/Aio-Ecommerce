<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{

    public $timestamps = false;

    protected $fillable = ['order_id', 'type', 'full_name', 'phone', 'street', 'ward', 'district', 'city', 'country'];

}
