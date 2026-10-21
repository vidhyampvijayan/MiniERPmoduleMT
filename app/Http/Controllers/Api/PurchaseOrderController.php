<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderStatusRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    public function store(StorePurchaseOrderRequest $request, PurchaseOrderService $service)
    {
        $order = $service->create($request->validated());

        return (new PurchaseOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(UpdatePurchaseOrderStatusRequest $request, $id, PurchaseOrderService $service)
    {
        $order = PurchaseOrder::with(['items', 'supplier'])->findOrFail($id);

        $updatedOrder = $service->transitionStatus(
            $order,
            $request->input('status')
        );

        return new PurchaseOrderResource($updatedOrder);
    }
}
