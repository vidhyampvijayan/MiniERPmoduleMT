<?php

namespace App\Http\Controllers\Web;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $totalActiveProducts = Product::where('is_active', true)->count();

        $lowStockCount = Product::where('is_active', true)
            ->where('stock_quantity', '<', 10)
            ->count();

        $totalExpenditure = PurchaseOrder::where('status', PurchaseOrderStatus::RECEIVED)
            ->sum('total_amount');

        $latestOrders = PurchaseOrder::with('supplier')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalActiveProducts',
            'lowStockCount',
            'totalExpenditure',
            'latestOrders'
        ));
    }
}
