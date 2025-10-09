<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'home_url', 'status'];

}
