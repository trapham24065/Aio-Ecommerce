<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{

    use HasFactory;

    protected $fillable
        = [
            'name',
            'code',
            'street',
            'city',
            'state',
            'postal_code',
            'country',
            'status',
        ];

}
