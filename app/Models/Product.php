<?php

// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'type',
        'file_path',
        'stock',
        'weight',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getMainImageAttribute()
    {
        return $this->images()->orderBy('order')->first();
    }
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->ofMany([
            'id' => 'min'
        ], function($query) {
            $query->where('is_main', true);
        });
    }

}
