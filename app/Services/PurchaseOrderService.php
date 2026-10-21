<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Exceptions\InvalidOrderStateException;
use App\Exceptions\InvalidStockOperationException;
use App\Exceptions\OrderImmutableException;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $calculation = $this->calculateTotals($data['items'] ?? []);

            $order = PurchaseOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'supplier_id' => $data['supplier_id'],
                'status' => PurchaseOrderStatus::DRAFT,
                'total_amount' => $calculation['total_amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($calculation['items'] as $itemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);
            }

            return $order->load(['supplier', 'items.product']);
        });
    }

    public function transitionStatus(PurchaseOrder $order, $newStatus)
    {
        $targetStatus = $newStatus instanceof PurchaseOrderStatus
            ? $newStatus
            : PurchaseOrderStatus::from($newStatus);

        if ($order->status->isImmutable()) {
            throw new OrderImmutableException("Order {$order->order_number} is immutable in status {$order->status->value}.");
        }

        if (! $order->status->canTransitionTo($targetStatus)) {
            throw new InvalidOrderStateException("Cannot transition order {$order->order_number} from {$order->status->value} to {$targetStatus->value}.");
        }

        return DB::transaction(function () use ($order, $targetStatus) {
            if ($targetStatus === PurchaseOrderStatus::RECEIVED) {
                $order->loadMissing('items');

                $productIds = $order->items->pluck('product_id')->unique()->all();

                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($order->items as $item) {
                    $product = $products->get($item->product_id);

                    if (! $product) {
                        throw new InvalidStockOperationException("Product {$item->product_id} not found.");
                    }

                    $product->incrementStock($item->quantity);
                }

                $order->received_at = now();
            } elseif ($targetStatus === PurchaseOrderStatus::APPROVED) {
                $order->approved_at = now();
            } elseif ($targetStatus === PurchaseOrderStatus::CANCELLED) {
                $order->cancelled_at = now();
            }

            $order->status = $targetStatus;
            $order->save();

            return $order->fresh(['supplier', 'items.product']);
        });
    }

    public function calculateTotals(array $items)
    {
        if (empty($items)) {
            throw new InvalidStockOperationException('Purchase order must contain at least one item.');
        }

        $calculatedItems = [];
        $grandTotal = 0.0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0.0);

            if ($quantity <= 0) {
                throw new InvalidStockOperationException('Quantity must be greater than zero.');
            }

            if ($unitPrice < 0) {
                throw new InvalidStockOperationException('Unit price cannot be negative.');
            }

            $subtotal = PurchaseOrderItem::computeSubtotal($quantity, $unitPrice);
            $grandTotal += $subtotal;

            $calculatedItems[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'subtotal' => $subtotal,
            ];
        }

        return [
            'items' => $calculatedItems,
            'total_amount' => round($grandTotal, 2),
        ];
    }

    protected function generateOrderNumber()
    {
        return 'PO-'.date('Ymd').'-'.strtoupper(Str::random(6));
    }
}
