@extends('layouts.app', ['title' => 'Purchase Order ' . $order->order_number])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('purchase-orders.index') }}" class="text-decoration-none small text-muted">&larr; Back to orders</a>
        <h3 class="mb-0 mt-1">{{ $order->order_number }}</h3>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $order->status->badgeClass() }} fs-6">
            {{ $order->status->label() }}
        </span>

        @if ($order->status === \App\Enums\PurchaseOrderStatus::DRAFT)
            <form method="POST" action="{{ route('purchase-orders.update-status', $order->id) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="APPROVED">
                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
            </form>

            <form method="POST" action="{{ route('purchase-orders.update-status', $order->id) }}" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                @csrf
                <input type="hidden" name="status" value="CANCELLED">
                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
            </form>
        @elseif ($order->status === \App\Enums\PurchaseOrderStatus::APPROVED)
            <form method="POST" action="{{ route('purchase-orders.update-status', $order->id) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="RECEIVED">
                <button type="submit" class="btn btn-success btn-sm">Mark as Received</button>
            </form>

            <form method="POST" action="{{ route('purchase-orders.update-status', $order->id) }}" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                @csrf
                <input type="hidden" name="status" value="CANCELLED">
                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
            </form>
        @endif
    </div>
</div>

@if ($order->status === \App\Enums\PurchaseOrderStatus::RECEIVED)
    <div class="alert alert-success">
        Order is marked as RECEIVED. Stock inventory has been updated.
    </div>
@elseif ($order->status === \App\Enums\PurchaseOrderStatus::CANCELLED)
    <div class="alert alert-secondary">
        Order is CANCELLED.
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-2">
                <strong>Supplier</strong>
            </div>
            <div class="card-body">
                <h5 class="mb-1">{{ $order->supplier->name }}</h5>
                <p class="mb-1 text-muted">{{ $order->supplier->email }}</p>
                <p class="mb-0 text-muted">{{ $order->supplier->phone }} | {{ $order->supplier->address }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-2">
                <strong>Details</strong>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Created:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p class="mb-1"><strong>Approved:</strong> {{ $order->approved_at ? $order->approved_at->format('Y-m-d H:i') : '-' }}</p>
                <p class="mb-1"><strong>Received:</strong> {{ $order->received_at ? $order->received_at->format('Y-m-d H:i') : '-' }}</p>
                @if ($order->notes)
                    <p class="mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-2">
        <strong>Items</strong>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <div>{{ $item->product->name }}</div>
                            <small class="text-muted">Current Stock: {{ $item->product->stock_quantity }}</small>
                        </td>
                        <td><code>{{ $item->product->sku }}</code></td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">${{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold fs-5 text-primary">
                        ${{ number_format((float) $order->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
