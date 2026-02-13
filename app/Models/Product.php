<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'product_name',
        'product_code',
        'category_id',
        'description',
        'color',
        'finishing',
        'shopee_url',
        'tokopedia_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category of this product
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get all materials for this product (many-to-many)
     */
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'product_materials', 'product_id', 'material_id');
    }

    /**
     * Get all images for this product
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    /**
     * Get the size image for this product (only one)
     */
    public function sizeImage()
    {
        return $this->hasOne(ProductSizeImage::class, 'product_id');
    }
}