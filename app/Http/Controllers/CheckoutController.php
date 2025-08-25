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
    public function index(Request $request)
    {
        $user = Auth::user();
        $cart = $this->cartService->getCart($user, $request);

        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Tu carrito está vacío. Agrega productos antes de continuar.');
        }

        $cart->load('items');

        // Si hay usuario autenticado, envía direcciones; si no, solo el carrito
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
        $user = Auth::user();

        if ($user) {
            $validated = $request->validate([
                'address_id' => 'required|exists:addresses,id',
            ]);
            $cart = $this->cartService->getCart($user, $request);
            $address = Address::findOrFail($validated['address_id']);

            if ($address->user_id !== $user->id) {
                return redirect()->back()->with('error', 'Dirección inválida.');
            }

            $this->cartService->setShippingAddress($cart, $address);
        } else {
            // Invitado: validar datos de contacto y dirección
            $validated = $request->validate([
                'guest_email' => 'required|email',
                'guest_phone' => 'required|string|max:20',
                'guest_name' => 'required|string|max:255',
                'shipping_address' => 'required|array',
                'shipping_address.line_1' => 'required|string|max:255',
                'shipping_address.line_2' => 'nullable|string|max:255',
                'shipping_address.city' => 'required|string|max:255',
                'shipping_address.state' => 'required|string|max:255',
                'shipping_address.zip' => 'required|string|max:20',
            ]);
            $cart = $this->cartService->getCart(null, $request);

            // Guarda el snapshot de la dirección en el carrito (o en sesión)
            $cart->shipping_address_snapshot = json_encode($validated['shipping_address']);
            $cart->guest_email = $validated['guest_email'];
            $cart->guest_phone = $validated['guest_phone'];
            $cart->guest_name = $validated['guest_name'];
            $cart->save();
        }

        return redirect()->route('checkout.payment');
    }

    /**
     * Show the payment page.
     */
    public function payment(Request $request)
    {
        $user = Auth::user();
        $cart = $this->cartService->getCart($user, $request);

        if (!$cart || (!$user && !$cart->shipping_address_snapshot) || ($user && !$cart->shipping_address_id)) {
            return redirect()->route('checkout.index')
                ->with('error', 'Selecciona una dirección de envío primero.');
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
            'card_number' => 'required|string|size:19',
            'card_holder' => 'required|string|max:255',
            'expiry_date' => 'required|string|size:5|date_format:m/y',
            'cvv' => 'required|string|size:3',
        ]);

        $user = Auth::user();
        $cart = $this->cartService->getCart($user, $request);

        // Validación de pago de prueba
        if ($validated['card_number'] !== '4111 1111 1111 1111' || $validated['card_holder'] === 'FAIL') {
            return redirect()->back()
                ->withErrors(['payment' => 'El pago fue rechazado. Verifica los datos e intenta de nuevo.']);
        }

        try {
            DB::beginTransaction();

            $orderData = [
                'user_id' => $user->id ?? null,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_fee' => $cart->shipping_fee,
                'total' => $cart->total,
                'status' => 'pending',
                'order_number' => Order::generateOrderNumber(),
            ];

            if ($user) {
                $orderData['shipping_address_id'] = $cart->shipping_address_id;
                $orderData['shipping_address_snapshot'] = $cart->shippingAddress ? json_encode($cart->shippingAddress->toArray()) : null;
            } else {
                $orderData['guest_email'] = $cart->guest_email;
                $orderData['guest_phone'] = $cart->guest_phone;
                $orderData['guest_name'] = $cart->guest_name;
                $orderData['shipping_address_snapshot'] = $cart->shipping_address_snapshot;
            }

            $order = Order::create($orderData);

            foreach ($cart->items as $item) {
                if ($item->stock_quantity < $item->pivot->quantity) {
                    throw new \Exception("Stock insuficiente para {$item->name}. Solo quedan {$item->stock_quantity} unidades.");
                }
                $item->decrement('stock_quantity', $item->pivot->quantity);
                $order->items()->attach($item->id, [
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity,
                    'price' => $item->pivot->price,
                    'subtotal' => $item->pivot->quantity * $item->pivot->price
                ]);
            }

            $this->cartService->clearCart($cart);

            DB::commit();

            return redirect()->route('orders.show', $order->id)
                ->with('success', '¡Pedido realizado con éxito!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['payment' => 'Ocurrió un error al procesar tu pedido. Intenta de nuevo.']);
        }
    }
}
