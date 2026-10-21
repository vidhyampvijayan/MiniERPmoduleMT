<?php

namespace Tests\Unit;

use App\Enums\PurchaseOrderStatus;
use App\Exceptions\InvalidStockOperationException;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_increments_correctly(): void
    {
        $product = Product::create([
            'sku' => 'SKU-TEST-01',
            'name' => 'Test Unit',
            'unit_price' => 20.00,
            'stock_quantity' => 15,
        ]);

        $this->assertEquals(15, $product->stock_quantity);

        $product->incrementStock(10);
        $product->refresh();

        $this->assertEquals(25, $product->stock_quantity);
    }

    public function test_rejects_negative_or_zero_stock_increment(): void
    {
        $product = Product::create([
            'sku' => 'SKU-TEST-02',
            'name' => 'Test Unit 2',
            'unit_price' => 30.00,
            'stock_quantity' => 5,
        ]);

        $this->expectException(InvalidStockOperationException::class);
        $product->incrementStock(-5);
    }

    public function test_rejects_zero_stock_increment(): void
    {
        $product = Product::create([
            'sku' => 'SKU-TEST-03',
            'name' => 'Test Unit 3',
            'unit_price' => 30.00,
            'stock_quantity' => 5,
        ]);

        $this->expectException(InvalidStockOperationException::class);
        $product->incrementStock(0);
    }

    public function test_lifecycle_status_transitions(): void
    {
        $this->assertTrue(PurchaseOrderStatus::DRAFT->canTransitionTo(PurchaseOrderStatus::APPROVED));
        $this->assertTrue(PurchaseOrderStatus::DRAFT->canTransitionTo(PurchaseOrderStatus::CANCELLED));
        $this->assertFalse(PurchaseOrderStatus::DRAFT->canTransitionTo(PurchaseOrderStatus::RECEIVED));

        $this->assertTrue(PurchaseOrderStatus::APPROVED->canTransitionTo(PurchaseOrderStatus::RECEIVED));
        $this->assertTrue(PurchaseOrderStatus::APPROVED->canTransitionTo(PurchaseOrderStatus::CANCELLED));
        $this->assertFalse(PurchaseOrderStatus::APPROVED->canTransitionTo(PurchaseOrderStatus::DRAFT));

        $this->assertFalse(PurchaseOrderStatus::RECEIVED->canTransitionTo(PurchaseOrderStatus::DRAFT));
        $this->assertFalse(PurchaseOrderStatus::RECEIVED->canTransitionTo(PurchaseOrderStatus::APPROVED));
        $this->assertFalse(PurchaseOrderStatus::RECEIVED->canTransitionTo(PurchaseOrderStatus::CANCELLED));

        $this->assertTrue(PurchaseOrderStatus::RECEIVED->isImmutable());
        $this->assertTrue(PurchaseOrderStatus::CANCELLED->isImmutable());
        $this->assertFalse(PurchaseOrderStatus::DRAFT->isImmutable());
        $this->assertFalse(PurchaseOrderStatus::APPROVED->isImmutable());
    }
}
