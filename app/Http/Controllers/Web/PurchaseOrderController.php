<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseOrderRequest $request, PurchaseOrderService $service)
    {
        $order = $service->create($request->validated());

        return redirect()
            ->route('purchase-orders.show', $order->id)
            ->with('success', "Purchase order {$order->order_number} created successfully with status DRAFT.");
    }

    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier', 'items.product'])->findOrFail($id);

        return view('purchase-orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id, PurchaseOrderService $service)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:APPROVED,RECEIVED,CANCELLED'],
        ]);

        $order = PurchaseOrder::with(['supplier', 'items.product'])->findOrFail($id);

        $service->transitionStatus($order, $validated['status']);

        return redirect()
            ->route('purchase-orders.show', $order->id)
            ->with('success', "Order {$order->order_number} successfully transitioned to {$validated['status']}.");
    }
}
