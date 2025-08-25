<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'line_1',
        'line_2',
        'city',
        'state',
        'zip',
        'is_default'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the carts that use this address for shipping.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class, 'shipping_address_id');
    }

    /**
     * Get the orders that use this address for shipping.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }
}
