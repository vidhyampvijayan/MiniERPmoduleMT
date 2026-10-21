<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseOrderApiSecurityAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_requests_without_sanctum_token_return_401(): void
    {
        $response = $this->postJson('/api/purchase-orders', [
            'supplier_id' => 1,
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'unit_price' => 25.00],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_creating_po_with_missing_required_fields_fails_validation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/purchase-orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'items']);
    }

    public function test_creating_po_with_empty_items_array_fails_validation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $supplier = Supplier::factory()->create();

        $response = $this->postJson('/api/purchase-orders', [
            'supplier_id' => $supplier->id,
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_creating_po_with_invalid_item_data_fails_validation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $supplier = Supplier::factory()->create();

        $response = $this->postJson('/api/purchase-orders', [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 0,
                    'unit_price' => -5.00,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.product_id',
                'items.0.quantity',
                'items.0.unit_price',
            ]);
    }

    public function test_creating_po_with_valid_payload_returns_201_and_sets_draft_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['unit_price' => 50.00]);

        $payload = [
            'supplier_id' => $supplier->id,
            'notes' => 'Urgent replenishment batch',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'unit_price' => 48.50,
                ],
            ],
        ];

        $response = $this->postJson('/api/purchase-orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', PurchaseOrderStatus::DRAFT->value)
            ->assertJsonPath('data.supplier.id', $supplier->id)
            ->assertJsonCount(1, 'data.items');

        $this->assertEquals(194.00, (float) $response->json('data.total_amount'));

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::DRAFT->value,
            'total_amount' => 194.00,
        ]);
    }
}
