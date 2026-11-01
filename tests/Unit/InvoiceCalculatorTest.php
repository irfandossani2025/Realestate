<?php

namespace Tests\Unit;

use App\Services\InvoiceCalculator;
use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    public function test_it_calculates_configurable_vat_in_omr_to_three_decimals(): void
    {
        $amounts = (new InvoiceCalculator)->calculate(1, 125.750, 5);

        $this->assertSame(125.750, $amounts['subtotal']);
        $this->assertSame(6.288, $amounts['vat_amount']);
        $this->assertSame(132.038, $amounts['total']);
    }

    public function test_zero_rated_line_has_no_vat(): void
    {
        $amounts = (new InvoiceCalculator)->calculate(2, 15.125, 0);

        $this->assertSame(30.250, $amounts['subtotal']);
        $this->assertSame(0.0, $amounts['vat_amount']);
        $this->assertSame(30.250, $amounts['total']);
    }
}
