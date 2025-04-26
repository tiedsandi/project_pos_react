<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'photo',
        'description',
        'price',
        'stock',
        'is_active'
    ];
}
