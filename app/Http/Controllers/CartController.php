<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Item;
use App\Services\CartService;
use App\Services\TaxService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
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
     * Display the user's shopping cart.
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getCart(Auth::user(), $request);
        $cart->load('items');

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'shipping' => $cart->shipping_fee,
            'total' => $cart->total
        ]);
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $this->cartService->getCart(Auth::user(), $request);
        $item = Item::findOrFail($validated['item_id']);

        try {
            $this->cartService->addItem($cart, $item, $validated['quantity']);
            return redirect()->back()->with('success', 'Producto agregado al carrito correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified cart.
     */
    public function show(Cart $cart)
    {
        // Typically not needed since we use index for the current user's cart
        abort(404);
    }

    /**
     * Update the specified cart item quantity.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $this->cartService->getCart(Auth::user(), $request);
        $item = Item::findOrFail($id);

        if ($validated['quantity'] == 0) {
            $this->cartService->removeItem($cart, $item);
            $message = 'Producto eliminado del carrito.';
            return redirect()->back()->with('success', $message);
        } else {
            try {
                $this->cartService->updateItemQuantity($cart, $item, $validated['quantity']);
                $message = 'Carrito actualizado correctamente.';
                return redirect()->back()->with('success', $message);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }

    /**
     * Remove the specified item from the cart.
     */
    public function destroy(Request $request, string $id)
    {
        $cart = $this->cartService->getCart(Auth::user(), $request);
        $item = Item::findOrFail($id);
        $this->cartService->removeItem($cart, $item);

    return redirect()->back()->with('success', 'Producto eliminado del carrito.');
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(Request $request)
    {
        $cart = $this->cartService->getCart(Auth::user(), $request);
        $this->cartService->clearCart($cart);

    return redirect()->back()->with('success', 'El carrito ha sido vaciado.');
    }
}
