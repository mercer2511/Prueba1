<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class OrderController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct()
    {
        // All order routes require authentication
    }
    
    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $orders = Auth::user()->orders()->orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Not used in this application as orders are created through checkout.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     * Not used in this application as orders are created through checkout.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        return Inertia::render('Orders/Show', [
            'order' => $order->load(['items.item', 'address'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Not used in this application as orders cannot be edited after creation.
     */
    public function edit(Order $order)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     * Not used in this application as orders cannot be edited by users.
     */
    public function update(Request $request, Order $order)
    {
        abort(404);
    }

    /**
     * Cancel the specified order if it's still pending.
     */
    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);
        
        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending orders can be cancelled.');
        }
        
        $order->status = 'cancelled';
        $order->save();
        
        return redirect()->route('orders.index')
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * Not used in this application as orders should not be deleted.
     */
    public function destroy(Order $order)
    {
        abort(404);
    }
}
