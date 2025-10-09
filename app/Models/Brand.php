<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'status'];

}
