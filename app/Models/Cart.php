<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'shipping_address_id',
        'subtotal',
        'tax',
        'shipping_fee',
        'total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address for the cart.
     */
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get the items in the cart.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'cart_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Calculate the cart totals.
     */
    public function calculateTotals()
    {
        // Calculate subtotal
        $subtotal = $this->items->sum(function ($item) {
            return $item->pivot->quantity * $item->pivot->price;
        });

        // Calculate tax (16%)
        $tax = $subtotal * 0.16;

        // Calculate total
        $total = $subtotal + $tax + $this->shipping_fee;

        // Update the cart
        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]);

        return $this;
    }
}
