<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => 'PO-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'supplier_id' => Supplier::factory(),
            'status' => PurchaseOrderStatus::DRAFT,
            'total_amount' => 0.00,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseOrderStatus::DRAFT,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseOrderStatus::APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function received(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseOrderStatus::RECEIVED,
            'approved_at' => now()->subDay(),
            'received_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseOrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
