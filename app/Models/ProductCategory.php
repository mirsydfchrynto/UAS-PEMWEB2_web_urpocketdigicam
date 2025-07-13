<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'hub_category_id',
    ];

    /**
     * Relationship dengan Product
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    /**
     * Scope untuk kategori yang sudah sync ke Hub
     */
    public function scopeSyncedToHub($query)
    {
        return $query->whereNotNull('hub_category_id');
    }

    /**
     * Scope untuk kategori yang belum sync ke Hub
     */
    public function scopeNotSyncedToHub($query)
    {
        return $query->whereNull('hub_category_id');
    }
}