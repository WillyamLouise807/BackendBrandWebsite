<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'material_name',
    ];

    /**
     * Get all products using this material
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_materials', 'material_id', 'product_id');
    }
}