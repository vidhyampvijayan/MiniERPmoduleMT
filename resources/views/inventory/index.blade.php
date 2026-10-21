@extends('layouts.app', ['title' => 'Inventory Overview'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Inventory Overview</h3>
    </div>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-outline-primary">+ Create PO</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-center">Current Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="{{ $product->stock_quantity < 10 ? 'table-warning' : '' }}">
                        <td><code>{{ $product->sku }}</code></td>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            @if ($product->description)
                                <small class="text-muted">{{ $product->description }}</small>
                            @endif
                        </td>
                        <td class="text-end">${{ number_format((float) $product->unit_price, 2) }}</td>
                        <td class="text-center fw-bold {{ $product->stock_quantity < 10 ? 'text-danger' : '' }}">
                            {{ $product->stock_quantity }}
                        </td>
                        <td>
                            @if ($product->stock_quantity < 10)
                                <span class="badge bg-danger">Low Stock Alert (&lt; 10)</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($products->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
