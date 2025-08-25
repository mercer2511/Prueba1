<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $items = \App\Models\Item::all();
    return Inertia::render('Welcome', [
        'items' => $items
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    $orders = $user->orders()->orderBy('created_at', 'desc')->get();
    
    // Cálculo de estadísticas para el dashboard
    $stats = [
        'ordersCount' => $orders->count(),
        'pendingOrders' => $orders->where('status', 'processing')->count(),
        'totalSpent' => $orders->sum('total'),
        'lastOrderDate' => $orders->first() ? $orders->first()->created_at->format('d/m/Y') : null,
    ];
    
    // Obtener los últimos pedidos para mostrar en el dashboard
    $recentOrders = $orders->take(5);
    
    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'recentOrders' => $recentOrders
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de productos
Route::resource('items', \App\Http\Controllers\ItemController::class);

// Rutas de carrito
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [\App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
Route::put('/cart/{id}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [\App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');

// Rutas de checkout - requieren autenticación
Route::middleware(['auth'])->group(function () {
    // Direcciones
    Route::resource('addresses', \App\Http\Controllers\AddressController::class);
    Route::post('addresses/{address}/default', [\App\Http\Controllers\AddressController::class, 'setDefault'])->name('addresses.default');
    
    // Proceso de checkout
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/shipping', [\App\Http\Controllers\CheckoutController::class, 'shipping'])->name('checkout.shipping');
    Route::get('/checkout/payment', [\App\Http\Controllers\CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    
    // Órdenes
    Route::resource('orders', \App\Http\Controllers\OrderController::class)->only(['index', 'show']);
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
