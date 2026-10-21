<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\Supplier;

class SupplierReportService
{
    public function getSpendReport()
    {
        return Supplier::query()
            ->select('suppliers.id as supplier_id', 'suppliers.name as supplier_name', 'suppliers.email as supplier_email')
            ->selectRaw('ROUND(COALESCE(SUM(purchase_orders.total_amount), 0), 2) as total_spend')
            ->selectRaw('COUNT(purchase_orders.id) as received_orders_count')
            ->join('purchase_orders', function ($join) {
                $join->on('suppliers.id', '=', 'purchase_orders.supplier_id')
                    ->where('purchase_orders.status', '=', PurchaseOrderStatus::RECEIVED->value);
            })
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.email')
            ->orderByDesc('total_spend')
            ->get();
    }
}
