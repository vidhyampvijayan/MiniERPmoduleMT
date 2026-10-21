<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryAndReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_sanctum_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@minierp.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@minierp.test',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJsonPath('token_type', 'Bearer');
    }

    public function test_api_login_with_invalid_credentials_returns_401(): void
    {
        User::factory()->create([
            'email' => 'admin@minierp.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@minierp.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid email or password.']);
    }

    public function test_get_inventory_returns_paginated_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Product::factory()->count(12)->create();

        $response = $this->getJson('/api/inventory?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'sku', 'name', 'unit_price', 'stock_quantity', 'is_low_stock'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_get_low_stock_inventory_filters_items_below_threshold(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Product::factory()->create(['stock_quantity' => 2]);
        Product::factory()->create(['stock_quantity' => 8]);
        Product::factory()->create(['stock_quantity' => 0]);

        Product::factory()->create(['stock_quantity' => 15]);
        Product::factory()->create(['stock_quantity' => 50]);

        $response = $this->getJson('/api/inventory/low-stock?threshold=10');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $item) {
            $this->assertLessThan(10, $item['stock_quantity']);
        }
    }

    public function test_supplier_spend_report_aggregates_only_received_orders(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $supplierA = Supplier::factory()->create(['name' => 'Supplier Alpha']);
        $supplierB = Supplier::factory()->create(['name' => 'Supplier Beta']);

        PurchaseOrder::factory()->create([
            'supplier_id' => $supplierA->id,
            'status' => PurchaseOrderStatus::RECEIVED,
            'total_amount' => 500.00,
        ]);
        PurchaseOrder::factory()->create([
            'supplier_id' => $supplierA->id,
            'status' => PurchaseOrderStatus::RECEIVED,
            'total_amount' => 300.00,
        ]);
        PurchaseOrder::factory()->create([
            'supplier_id' => $supplierA->id,
            'status' => PurchaseOrderStatus::DRAFT,
            'total_amount' => 200.00,
        ]);

        PurchaseOrder::factory()->create([
            'supplier_id' => $supplierB->id,
            'status' => PurchaseOrderStatus::RECEIVED,
            'total_amount' => 450.00,
        ]);
        PurchaseOrder::factory()->create([
            'supplier_id' => $supplierB->id,
            'status' => PurchaseOrderStatus::CANCELLED,
            'total_amount' => 600.00,
        ]);

        $response = $this->getJson('/api/reports/supplier-spend');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['supplier_id', 'supplier_name', 'supplier_email', 'total_spend', 'received_orders_count'],
                ],
            ]);

        $data = collect($response->json('data'));

        $alphaReport = $data->firstWhere('supplier_id', $supplierA->id);
        $this->assertNotNull($alphaReport);
        $this->assertEquals(800.00, (float) $alphaReport['total_spend']);
        $this->assertEquals(2, $alphaReport['received_orders_count']);

        $betaReport = $data->firstWhere('supplier_id', $supplierB->id);
        $this->assertNotNull($betaReport);
        $this->assertEquals(450.00, (float) $betaReport['total_spend']);
        $this->assertEquals(1, $betaReport['received_orders_count']);
    }
}
