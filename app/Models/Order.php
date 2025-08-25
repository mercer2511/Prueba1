<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'shipping_address_id',
        'shipping_address_snapshot',
        'subtotal',
        'tax',
        'shipping_fee',
        'total',
        'payment_method',
        'payment_status',
        'transaction_id',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'guest_email',
        'guest_phone',
        'guest_name',
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
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address for the order.
     */
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get the items in the order.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_items')
                    ->withPivot('name', 'quantity', 'price', 'subtotal')
                    ->withTimestamps();
    }

    /**
     * Get the order items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber()
    {
        $prefix = date('Ymd');
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
                        ->orderBy('order_number', 'desc')
                        ->first();
        
        if ($lastOrder) {
            $lastNumber = substr($lastOrder->order_number, 8);
            $nextNumber = str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $nextNumber;
    }
}
