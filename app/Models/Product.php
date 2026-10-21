<?php

namespace App\Models;

use App\Exceptions\InvalidStockOperationException;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit_price',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'is_low_stock',
    ];

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock_quantity', '<', $threshold);
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity < $this->low_stock_threshold;
    }

    public function incrementStock($quantity)
    {
        if ($quantity <= 0) {
            throw new InvalidStockOperationException('Stock increment quantity must be greater than zero.');
        }

        $this->increment('stock_quantity', $quantity);
    }
}
