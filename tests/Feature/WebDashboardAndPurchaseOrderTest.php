<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebDashboardAndPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response2 = $this->get('/purchase-orders/create');
        $response2->assertRedirect('/login');

        $response3 = $this->get('/inventory');
        $response3->assertRedirect('/login');
    }

    public function test_dashboard_displays_correct_metrics_and_latest_orders(): void
    {
        $user = User::factory()->create();

        Product::factory()->create(['is_active' => true, 'stock_quantity' => 4]);
        Product::factory()->create(['is_active' => true, 'stock_quantity' => 20]);

        $supplier = Supplier::factory()->create();
        PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::RECEIVED,
            'total_amount' => 1250.50,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Executive Dashboard')
            ->assertSee('Total Active Products')
            ->assertSee('Low Stock Alerts')
            ->assertSee('Total Expenditure')
            ->assertSee('$1,250.50');
    }

    public function test_web_purchase_order_creation_and_lifecycle(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'unit_price' => 50.00]);

        $createResponse = $this->actingAs($user)->get('/purchase-orders/create');
        $createResponse->assertStatus(200)
            ->assertSee($supplier->name)
            ->assertSee($product->sku);

        $postResponse = $this->actingAs($user)->post('/purchase-orders', [
            'supplier_id' => $supplier->id,
            'notes' => 'Test Web PO creation',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 45.00,
                ],
            ],
        ]);

        $createdOrder = PurchaseOrder::first();
        $this->assertNotNull($createdOrder);
        $this->assertEquals(PurchaseOrderStatus::DRAFT, $createdOrder->status);
        $this->assertEquals(225.00, (float) $createdOrder->total_amount);

        $postResponse->assertRedirect(route('purchase-orders.show', $createdOrder->id));

        $showResponse = $this->actingAs($user)->get(route('purchase-orders.show', $createdOrder->id));
        $showResponse->assertStatus(200)
            ->assertSee('Approve')
            ->assertSee('Cancel');

        $approveResponse = $this->actingAs($user)->post(route('purchase-orders.update-status', $createdOrder->id), [
            'status' => 'APPROVED',
        ]);
        $approveResponse->assertRedirect(route('purchase-orders.show', $createdOrder->id));
        $createdOrder->refresh();
        $this->assertEquals(PurchaseOrderStatus::APPROVED, $createdOrder->status);

        $showApprovedResponse = $this->actingAs($user)->get(route('purchase-orders.show', $createdOrder->id));
        $showApprovedResponse->assertSee('Mark as Received');

        $receiveResponse = $this->actingAs($user)->post(route('purchase-orders.update-status', $createdOrder->id), [
            'status' => 'RECEIVED',
        ]);
        $receiveResponse->assertRedirect(route('purchase-orders.show', $createdOrder->id));
        $createdOrder->refresh();
        $product->refresh();

        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $createdOrder->status);
        $this->assertEquals(15, $product->stock_quantity);
    }

    public function test_inventory_overview_shows_items_and_flags_low_stock(): void
    {
        $user = User::factory()->create();

        $lowStockProduct = Product::factory()->create([
            'sku' => 'SKU-LOW-99',
            'name' => 'Low Stock Sensor',
            'stock_quantity' => 3,
        ]);

        $normalProduct = Product::factory()->create([
            'sku' => 'SKU-NORM-11',
            'name' => 'Abundant Part',
            'stock_quantity' => 50,
        ]);

        $response = $this->actingAs($user)->get('/inventory');

        $response->assertStatus(200)
            ->assertSee('SKU-LOW-99')
            ->assertSee('SKU-NORM-11')
            ->assertSee('Low Stock Alert (< 10)');
    }
}
