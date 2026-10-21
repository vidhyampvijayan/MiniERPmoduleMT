<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Exceptions\InvalidOrderStateException;
use App\Exceptions\OrderImmutableException;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PurchaseOrderService::class);
    }

    public function test_transitioning_order_to_received_automatically_and_accurately_increments_stock(): void
    {
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->create(['stock_quantity' => 10, 'unit_price' => 50.00]);
        $productB = Product::factory()->create(['stock_quantity' => 5, 'unit_price' => 25.00]);

        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::APPROVED,
            'total_amount' => 450.00,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $order->id,
            'product_id' => $productA->id,
            'quantity' => 6,
            'unit_price' => 50.00,
            'subtotal' => 300.00,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $order->id,
            'product_id' => $productB->id,
            'quantity' => 6,
            'unit_price' => 25.00,
            'subtotal' => 150.00,
        ]);

        $this->service->transitionStatus($order, PurchaseOrderStatus::RECEIVED);

        $productA->refresh();
        $productB->refresh();
        $order->refresh();

        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $order->status);
        $this->assertNotNull($order->received_at);
        $this->assertEquals(16, $productA->stock_quantity);
        $this->assertEquals(11, $productB->stock_quantity);
    }

    public function test_attempting_to_transition_backwards_once_in_received_status_throws_exception_and_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = PurchaseOrder::factory()->received()->create();

        try {
            $this->service->transitionStatus($order, PurchaseOrderStatus::APPROVED);
            $this->fail('Expected OrderImmutableException was not thrown.');
        } catch (OrderImmutableException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        $response = $this->patchJson("/api/purchase-orders/{$order->id}/status", [
            'status' => 'APPROVED',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'ORDER_IMMUTABLE');
    }

    public function test_attempting_to_transition_once_in_cancelled_status_throws_exception_and_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = PurchaseOrder::factory()->cancelled()->create();

        try {
            $this->service->transitionStatus($order, PurchaseOrderStatus::APPROVED);
            $this->fail('Expected OrderImmutableException was not thrown.');
        } catch (OrderImmutableException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        $response = $this->patchJson("/api/purchase-orders/{$order->id}/status", [
            'status' => 'APPROVED',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'ORDER_IMMUTABLE');
    }

    public function test_invalid_lifecycle_transition_from_draft_directly_to_received_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = PurchaseOrder::factory()->draft()->create();

        try {
            $this->service->transitionStatus($order, PurchaseOrderStatus::RECEIVED);
            $this->fail('Expected InvalidOrderStateException was not thrown.');
        } catch (InvalidOrderStateException $e) {
            $this->assertStringContainsString('Cannot transition', $e->getMessage());
        }

        $response = $this->patchJson("/api/purchase-orders/{$order->id}/status", [
            'status' => 'RECEIVED',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'INVALID_ORDER_STATE_TRANSITION');
    }
}
