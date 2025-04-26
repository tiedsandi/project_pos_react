<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected $appends = ['photo_url', 'category_name'];

    public function getCategoryNameAttribute()
    {
        return Category::find($this->category_id)->name ?? null;
    }

    // protected function photo(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn($image) => url('/storage/products/' . $image),
    //     );
    // }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/products/' . $this->photo) : null;
    }
}
