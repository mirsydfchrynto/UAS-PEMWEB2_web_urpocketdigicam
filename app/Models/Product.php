<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'stock',
        'product_category_id',
        'image_url',
        'is_active',
        'hub_product_id',
        'is_visible',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Relationship dengan ProductCategory
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Scope untuk produk yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk produk yang visible
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope untuk produk yang sudah sync ke Hub
     */
    public function scopeSyncedToHub($query)
    {
        return $query->whereNotNull('hub_product_id');
    }

    /**
     * Scope untuk produk yang belum sync ke Hub
     */
    public function scopeNotSyncedToHub($query)
    {
        return $query->whereNull('hub_product_id');
    }

    /**
     * Accessor untuk formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}