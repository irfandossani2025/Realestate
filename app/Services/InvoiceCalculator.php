<?php

namespace App\Services;

class InvoiceCalculator
{
    /** @return array{subtotal: float, vat_amount: float, total: float} */
    public function calculate(float $quantity, float $unitPrice, float $vatRate): array
    {
        $quantityMilli = (int) round($quantity * 1000);
        $unitBaisa = (int) round($unitPrice * 1000);
        $subtotalBaisa = (int) round(($quantityMilli * $unitBaisa) / 1000);
        $vatBaisa = (int) round($subtotalBaisa * $vatRate / 100);

        return [
            'subtotal' => (float) ($subtotalBaisa / 1000),
            'vat_amount' => (float) ($vatBaisa / 1000),
            'total' => (float) (($subtotalBaisa + $vatBaisa) / 1000),
        ];
    }
}
