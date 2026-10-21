<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@minierp.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $suppliers = [
            [
                'name' => 'Test Company',
                'email' => 'test@gmail.com',
                'phone' => '9898555787',
                'contact_person' => 'Rahul',
                'address' => 'Test address 123',
            ],
            [
                'name' => 'ABC Traders Kochi',
                'email' => 'abc@gmail.com',
                'phone' => '9898555787',
                'contact_person' => 'Suresh',
                'address' => 'MG Road, Kochi, Kerala',
            ],
            [
                'name' => 'Malabar Supplies',
                'email' => 'malabar@gmail.com',
                'phone' => '9898555787',
                'contact_person' => 'Faisal',
                'address' => 'Calicut, Kerala',
            ],
            [
                'name' => 'KL Traders',
                'email' => 'kltraders@gmail.com',
                'phone' => '9898555787',
                'contact_person' => 'Anand',
                'address' => 'Ernakulam, Kerala',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $item) {
            $supplierModels[] = Supplier::firstOrCreate(['email' => $item['email']], $item);
        }

        $products = [
            ['sku' => 'SKU-001', 'name' => 'Test Item A', 'unit_price' => 50.00, 'stock_quantity' => 4, 'description' => 'Test desc'],
            ['sku' => 'SKU-002', 'name' => 'Test Item B', 'unit_price' => 120.00, 'stock_quantity' => 7, 'description' => 'Test desc'],
            ['sku' => 'SKU-003', 'name' => 'Relay Module 5V', 'unit_price' => 25.00, 'stock_quantity' => 50, 'description' => '5v relay'],
            ['sku' => 'SKU-004', 'name' => 'Sensor Test 1', 'unit_price' => 85.00, 'stock_quantity' => 3, 'description' => 'Sensor'],
            ['sku' => 'SKU-005', 'name' => 'Power Adapter 12V', 'unit_price' => 250.00, 'stock_quantity' => 15, 'description' => '12v adapter'],
            ['sku' => 'SKU-006', 'name' => 'Connector Cable', 'unit_price' => 15.00, 'stock_quantity' => 60, 'description' => 'Cable'],
            ['sku' => 'SKU-007', 'name' => 'Motor 12V', 'unit_price' => 180.00, 'stock_quantity' => 5, 'description' => 'Dc motor'],
            ['sku' => 'SKU-008', 'name' => 'LED Red', 'unit_price' => 5.00, 'stock_quantity' => 120, 'description' => 'Led bulb'],
            ['sku' => 'SKU-009', 'name' => 'Switch Test', 'unit_price' => 10.00, 'stock_quantity' => 35, 'description' => 'On off switch'],
            ['sku' => 'SKU-010', 'name' => 'Test Wire Bundle', 'unit_price' => 40.00, 'stock_quantity' => 2, 'description' => 'Wire bundle'],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $productModels[] = Product::firstOrCreate(
                ['sku' => $p['sku']],
                array_merge($p, [
                    'low_stock_threshold' => 10,
                    'is_active' => true,
                ])
            );
        }

        $po1 = PurchaseOrder::firstOrCreate(
            ['order_number' => 'PO-1001'],
            [
                'supplier_id' => $supplierModels[0]->id,
                'status' => PurchaseOrderStatus::RECEIVED,
                'total_amount' => 500.00,
                'notes' => 'test order 1',
                'approved_at' => now()->subDays(2),
                'received_at' => now()->subDay(),
            ]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'product_id' => $productModels[0]->id],
            ['quantity' => 10, 'unit_price' => 50.00, 'subtotal' => 500.00]
        );

        $po2 = PurchaseOrder::firstOrCreate(
            ['order_number' => 'PO-1002'],
            [
                'supplier_id' => $supplierModels[1]->id,
                'status' => PurchaseOrderStatus::RECEIVED,
                'total_amount' => 600.00,
                'notes' => 'kochi order',
                'approved_at' => now()->subDays(3),
                'received_at' => now()->subDay(),
            ]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po2->id, 'product_id' => $productModels[1]->id],
            ['quantity' => 5, 'unit_price' => 120.00, 'subtotal' => 600.00]
        );

        $po3 = PurchaseOrder::firstOrCreate(
            ['order_number' => 'PO-1003'],
            [
                'supplier_id' => $supplierModels[2]->id,
                'status' => PurchaseOrderStatus::APPROVED,
                'total_amount' => 250.00,
                'notes' => 'approved test order',
                'approved_at' => now()->subHours(5),
            ]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po3->id, 'product_id' => $productModels[2]->id],
            ['quantity' => 10, 'unit_price' => 25.00, 'subtotal' => 250.00]
        );

        $po4 = PurchaseOrder::firstOrCreate(
            ['order_number' => 'PO-1004'],
            [
                'supplier_id' => $supplierModels[0]->id,
                'status' => PurchaseOrderStatus::DRAFT,
                'total_amount' => 170.00,
                'notes' => 'draft order',
            ]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po4->id, 'product_id' => $productModels[3]->id],
            ['quantity' => 2, 'unit_price' => 85.00, 'subtotal' => 170.00]
        );
    }
}
