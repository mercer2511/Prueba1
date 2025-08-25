<?php

namespace App\Services;

use App\Models\Address;

class ShippingService
{
    /**
     * Calculate shipping rate based on address.
     * 
     * This is a simulation of a shipping API call.
     *
     * @param Address $address
     * @return string
     */
    public function calculateRate(Address $address): string
    {
        // In a real application, this would make an API call
        // to a shipping provider using the address details
        
        // For this example, we'll simulate different rates based on state
        $stateRates = [
            'CA' => 200.00,
            'NY' => 180.00,
            'TX' => 150.00,
            'FL' => 170.00,
            // Add more states as needed
        ];
        
        // Get shipping rate for the state, or use default rate
        $state = strtoupper($address->state);
        $rate = $stateRates[$state] ?? 150.00;
        
        // Convert to decimal string with 2 decimal places
        return number_format($rate, 2, '.', '');
    }
}
