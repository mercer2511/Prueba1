<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class AddressController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct()
    {
        // Aplicamos el middleware auth a todas las rutas de este controlador
    }

    /**
     * Display a listing of the user's addresses.
     */
    public function index()
    {
        $addresses = Auth::user()->addresses;
        
        return Inertia::render('Addresses/Index', [
            'addresses' => $addresses
        ]);
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        return Inertia::render('Addresses/Create');
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'line_1' => 'required|string|max:255',
            'line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'is_default' => 'boolean'
        ]);
        
        $address = new Address($validated);
        $address->user_id = Auth::id();
        $address->save();
        
        if ($request->input('is_default', false)) {
            $this->setAsDefault($address);
        }
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address created successfully.');
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address)
    {
        // Verificar que el usuario sea el propietario de la dirección
        if (Auth::id() !== $address->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        return Inertia::render('Addresses/Show', [
            'address' => $address
        ]);
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(Address $address)
    {
        // Verificar que el usuario sea el propietario de la dirección
        if (Auth::id() !== $address->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        return Inertia::render('Addresses/Edit', [
            'address' => $address
        ]);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, Address $address)
    {
        // Verificar que el usuario sea el propietario de la dirección
        if (Auth::id() !== $address->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'line_1' => 'required|string|max:255',
            'line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'is_default' => 'boolean'
        ]);
        
        $address->update($validated);
        
        if ($request->input('is_default', false)) {
            $this->setAsDefault($address);
        }
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address updated successfully.');
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Address $address)
    {
        // Verificar que el usuario sea el propietario de la dirección
        if (Auth::id() !== $address->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $address->delete();
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address deleted successfully.');
    }
    
    /**
     * Set the address as default for the user.
     */
    protected function setAsDefault(Address $address)
    {
        // First, unset all other addresses as default
        Address::where('user_id', Auth::id())
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
            
        // Then set this one as default
        $address->is_default = true;
        $address->save();
        
        return $address;
    }
    
    /**
     * Set an address as the default address.
     */
    public function setDefault(Address $address)
    {
        // Verificar que el usuario sea el propietario de la dirección
        if (Auth::id() !== $address->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->setAsDefault($address);
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address set as default successfully.');
    }
}
