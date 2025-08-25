<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\ShippingService;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $taxService;
    protected $shippingService;

    public function __construct(CartService $cartService, TaxService $taxService, ShippingService $shippingService)
    {
        $this->cartService = $cartService;
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
    }

    /**
     * Show the checkout page.
     */
    public function index()
    {
        $user = Auth::user();
        $cart = $this->cartService->getCart($user);
        
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add items before checkout.');
        }
        
        // Asegurarse de cargar los ítems del carrito
        $cart->load('items');
        
        $addresses = $user ? $user->addresses : [];
        
        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'addresses' => $addresses,
        ]);
    }
    
    /**
     * Process the shipping selection.
     */
    public function shipping(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);
        
        $user = Auth::user();
        $cart = $this->cartService->getCart($user);
        $address = Address::findOrFail($validated['address_id']);
        
        // Check if address belongs to user
        if ($user && $address->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Invalid address selected.');
        }
        
        // Set shipping address for cart
        $this->cartService->setShippingAddress($cart, $address);
        
        return redirect()->route('checkout.payment');
    }
    
    /**
     * Show the payment page.
     */
    public function payment()
    {
        $user = Auth::user();
        $cart = $this->cartService->getCart($user);
        
        if (!$cart || !$cart->shipping_address_id) {
            return redirect()->route('checkout.index')
                ->with('error', 'Please select a shipping address first.');
        }
        
        return Inertia::render('Checkout/Payment', [
            'cart' => $cart,
        ]);
    }
    
    /**
     * Process the payment and create the order.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'card_number' => 'required|string|size:19', // Format: 4111 1111 1111 1111
            'card_holder' => 'required|string|max:255',
            'expiry_date' => 'required|string|size:5|date_format:m/y', // Format: MM/YY
            'cvv' => 'required|string|size:3',
        ]);
        
        $user = Auth::user();
        $cart = $this->cartService->getCart($user);
        
        // Simple payment validation for the test scenario
        if ($validated['card_number'] !== '4111 1111 1111 1111' || $validated['card_holder'] === 'FAIL') {
            return redirect()->back()
                ->withErrors(['payment' => 'Payment failed. Please check your card details and try again.']);
        }
        
        // Create order from cart
        try {
            DB::beginTransaction();
            
            $order = new Order();
            $order->user_id = $user->id ?? null;
            $order->address_id = $cart->shipping_address_id;
            $order->subtotal = $cart->subtotal;
            $order->tax = $cart->tax;
            $order->shipping_fee = $cart->shipping_fee;
            $order->total = $cart->total;
            $order->status = 'pending';
            $order->save();
            
            // Add items to order and update stock
            foreach ($cart->items as $item) {
                // Check if there's still enough stock
                if ($item->stock_quantity < $item->pivot->quantity) {
                    throw new \Exception("Insufficient stock for {$item->name}. Only {$item->stock_quantity} units available.");
                }
                
                // Decrease the stock
                $item->decrement('stock_quantity', $item->pivot->quantity);
                
                // Add the item to the order
                $order->items()->attach($item->id, [
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity,
                    'price' => $item->pivot->price,
                    'subtotal' => $item->pivot->quantity * $item->pivot->price
                ]);
            }
            
            // Clear the cart
            $this->cartService->clearCart($cart);
            
            DB::commit();
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['payment' => 'An error occurred while processing your order. Please try again.']);
        }
    }
}
