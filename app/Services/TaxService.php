<?php

namespace App\Services;

class TaxService
{
    /**
     * Tax rate as a decimal (16%)
     */
    const TAX_RATE = 0.16;

    /**
     * Calculate tax amount based on subtotal.
     *
     * @param float|string|int $subtotal
     * @return string
     */
    public function calculateTax($subtotal): string
    {
        // Convert to decimal string with 2 decimal places
        return number_format($subtotal * self::TAX_RATE, 2, '.', '');
    }
}
