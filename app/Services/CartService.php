<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    protected $taxService;
    protected $shippingService;

    /**
     * Create a new service instance.
     *
     * @param TaxService $taxService
     * @param ShippingService $shippingService
     */
    public function __construct(TaxService $taxService, ShippingService $shippingService)
    {
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
    }

    /**
     * Get or create a cart for the current session or user.
     *
     * @param User|null $user
     * @return Cart
     */
    public function getCart(?User $user = null, $request = null): Cart
    {
        if ($user) {
            // Get or create a cart for authenticated user
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'session_id' => null],
                ['subtotal' => 0, 'tax' => 0, 'shipping_fee' => 0, 'total' => 0]
            );
        } else {
            // Usa el session_id del request si está disponible, si no usa Session::getId()
            $sessionId = $request && method_exists($request, 'session')
                ? $request->session()->getId()
                : Session::getId();

            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'user_id' => null],
                ['subtotal' => 0, 'tax' => 0, 'shipping_fee' => 0, 'total' => 0]
            );
        }

        return $cart;
    }

    /**
     * Add an item to the cart.
     *
     * @param Cart $cart
     * @param Item $item
     * @param int $quantity
     * @return Cart
     * @throws \Exception If there is insufficient stock
     */
    public function addItem(Cart $cart, Item $item, int $quantity = 1): Cart
    {
        // Check if item has enough stock
        if ($item->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock. Only {$item->stock_quantity} units available.");
        }

        // Check if item already exists in cart
        $existingItem = $cart->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            // Calculate new total quantity
            $newQuantity = $existingItem->pivot->quantity + $quantity;

            // Check if the new total quantity exceeds available stock
            if ($newQuantity > $item->stock_quantity) {
                throw new \Exception("Insufficient stock. Only {$item->stock_quantity} units available.");
            }

            // Update quantity if item already exists
            $cart->items()->updateExistingPivot($item->id, [
                'quantity' => $newQuantity
            ]);
        } else {
            // Add new item to cart
            $cart->items()->attach($item->id, [
                'quantity' => $quantity,
                'price' => $item->price
            ]);
        }

        // Recalculate cart totals
        return $this->updateCartTotals($cart);
    }

    /**
     * Update item quantity in cart.
     *
     * @param Cart $cart
     * @param Item $item
     * @param int $quantity
     * @return Cart
     * @throws \Exception If there is insufficient stock
     */
    public function updateItemQuantity(Cart $cart, Item $item, int $quantity): Cart
    {
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $cart->items()->detach($item->id);
        } else {
            // Check if item has enough stock
            if ($quantity > $item->stock_quantity) {
                throw new \Exception("Insufficient stock. Only {$item->stock_quantity} units available.");
            }

            // Update item quantity
            $cart->items()->updateExistingPivot($item->id, [
                'quantity' => $quantity
            ]);
        }

        // Recalculate cart totals
        return $this->updateCartTotals($cart);
    }

    /**
     * Remove an item from the cart.
     *
     * @param Cart $cart
     * @param Item $item
     * @return Cart
     */
    public function removeItem(Cart $cart, Item $item): Cart
    {
        $cart->items()->detach($item->id);

        // Recalculate cart totals
        return $this->updateCartTotals($cart);
    }

    /**
     * Set shipping address for the cart.
     *
     * @param Cart $cart
     * @param Address $address
     * @return Cart
     */
    public function setShippingAddress(Cart $cart, Address $address): Cart
    {
        $cart->shipping_address_id = $address->id;
        $cart->save();

        // Calculate shipping fee
        $shippingFee = $this->shippingService->calculateRate($address);

        // Update cart with shipping fee
        $cart->update(['shipping_fee' => $shippingFee]);

        // Recalculate cart totals
        return $this->updateCartTotals($cart);
    }

    /**
     * Update cart totals.
     *
     * @param Cart $cart
     * @return Cart
     */
    public function updateCartTotals(Cart $cart): Cart
    {
        // Reload cart with items
        $cart->load('items');

        // Calculate subtotal
        $subtotal = $cart->items->sum(function ($item) {
            return $item->pivot->quantity * $item->pivot->price;
        });

        // Format subtotal as string with 2 decimal places
        $subtotal = number_format($subtotal, 2, '.', '');

        // Calculate tax
        $tax = $this->taxService->calculateTax($subtotal);

        // Get shipping fee (ensure it's a string with 2 decimal places)
        $shipping = $cart->shipping_fee ? number_format((float)$cart->shipping_fee, 2, '.', '') : '0.00';

        // Calculate total (convert all values to float for calculation, then back to string)
        $total = number_format((float)$subtotal + (float)$tax + (float)$shipping, 2, '.', '');

        // Update cart
        $cart->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]);

        return $cart;
    }

    /**
     * Transfer a guest cart to user cart on login.
     *
     * @param string $sessionId
     * @param User $user
     * @return Cart|null
     */
    public function transferCartToUser(string $sessionId, User $user): ?Cart
    {
        // Find session cart
        $sessionCart = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (!$sessionCart) {
            return null;
        }

        // Find or create user cart
        $userCart = $this->getCart($user);

        // Transfer items from session cart to user cart
        foreach ($sessionCart->items as $item) {
            $this->addItem($userCart, $item, $item->pivot->quantity);
        }

        // Transfer shipping address if set
        if ($sessionCart->shipping_address_id) {
            // Actualizar la dirección de envío y la tarifa
            $userCart->update([
                'shipping_address_id' => $sessionCart->shipping_address_id,
                'shipping_fee' => number_format((float)$sessionCart->shipping_fee, 2, '.', '')
            ]);
        }

        // Delete session cart
        $sessionCart->delete();

        // Recalculate user cart totals
        return $this->updateCartTotals($userCart);
    }

    /**
     * Clear the cart.
     *
     * @param Cart $cart
     * @return Cart
     */
    public function clearCart(Cart $cart): Cart
    {
        $cart->items()->detach();

        $cart->update([
            'subtotal' => 0,
            'tax' => 0,
            'shipping_fee' => 0,
            'total' => 0,
            'shipping_address_id' => null
        ]);

        return $cart;
    }
}
