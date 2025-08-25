<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_path',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the carts that contain this item.
     */
    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Get the orders that contain this item.
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('name', 'quantity', 'price', 'subtotal')
                    ->withTimestamps();
    }
    
    /**
     * Check if there is sufficient stock for the requested quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }
    
    /**
     * Check if the item is available (has stock and is active).
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }
}
