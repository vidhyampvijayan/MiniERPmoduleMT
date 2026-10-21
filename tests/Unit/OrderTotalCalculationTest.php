<?php

namespace Tests\Unit;

use App\Exceptions\InvalidStockOperationException;
use App\Models\PurchaseOrderItem;
use App\Services\PurchaseOrderService;
use PHPUnit\Framework\TestCase;

class OrderTotalCalculationTest extends TestCase
{
    private PurchaseOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseOrderService;
    }

    public function test_line_item_subtotal_calculation(): void
    {
        $subtotal = PurchaseOrderItem::computeSubtotal(5, 19.99);
        $this->assertEquals(99.95, $subtotal);

        $subtotal2 = PurchaseOrderItem::computeSubtotal(10, 4.50);
        $this->assertEquals(45.00, $subtotal2);
    }

    public function test_accurate_calculation_of_multiple_line_items_and_grand_total(): void
    {
        $items = [
            ['product_id' => 1, 'quantity' => 3, 'unit_price' => 15.25],
            ['product_id' => 2, 'quantity' => 2, 'unit_price' => 100.50],
            ['product_id' => 3, 'quantity' => 10, 'unit_price' => 2.99],
        ];

        $result = $this->service->calculateTotals($items);

        $this->assertCount(3, $result['items']);
        $this->assertEquals(45.75, $result['items'][0]['subtotal']);
        $this->assertEquals(201.00, $result['items'][1]['subtotal']);
        $this->assertEquals(29.90, $result['items'][2]['subtotal']);
        $this->assertEquals(276.65, $result['total_amount']);
    }

    public function test_rejects_empty_items_list(): void
    {
        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('Purchase order must contain at least one item.');

        $this->service->calculateTotals([]);
    }

    public function test_rejects_zero_or_negative_quantity(): void
    {
        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero.');

        $this->service->calculateTotals([
            ['product_id' => 1, 'quantity' => 0, 'unit_price' => 15.00],
        ]);
    }

    public function test_rejects_negative_unit_price(): void
    {
        $this->expectException(InvalidStockOperationException::class);
        $this->expectExceptionMessage('Unit price cannot be negative.');

        $this->service->calculateTotals([
            ['product_id' => 1, 'quantity' => 5, 'unit_price' => -10.00],
        ]);
    }
}
