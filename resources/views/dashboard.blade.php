@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Executive Dashboard</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">Create Purchase Order</a>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">View Inventory</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-2">Total Active Products</h6>
                <h2 class="card-title fw-bold mb-0">{{ number_format($totalActiveProducts) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-2">Low Stock Alerts</h6>
                <h2 class="card-title fw-bold {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($lowStockCount) }}
                </h2>
                <small class="text-muted">Items with quantity &lt; 10</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-2">Total Expenditure</h6>
                <h2 class="card-title fw-bold text-dark mb-0">${{ number_format($totalExpenditure, 2) }}</h2>
                <small class="text-muted">All RECEIVED orders</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Latest Purchase Orders</h5>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Total Value</th>
                    <th>Created Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('purchase-orders.show', $order->id) }}" class="fw-semibold text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $order->supplier->name }}</div>
                            <small class="text-muted">{{ $order->supplier->email }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $order->status->badgeClass() }}">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td class="fw-bold">${{ number_format((float) $order->total_amount, 2) }}</td>
                        <td class="text-muted small">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No purchase orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
