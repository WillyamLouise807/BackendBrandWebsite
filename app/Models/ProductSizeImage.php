<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSizeImage extends Model
{
    use HasFactory;

    protected $table = 'product_size_images';

    protected $fillable = [
        'product_id',
        'image_url',
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}