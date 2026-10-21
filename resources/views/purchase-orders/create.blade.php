@extends('layouts.app', ['title' => 'Create Purchase Order'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Create Purchase Order</h3>
    </div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Back to Orders</a>
</div>

<form method="POST" action="{{ route('purchase-orders.store') }}" id="poForm">
    @csrf

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Order Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">Supplier *</label>
                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="" disabled {{ old('supplier_id') ? '' : 'selected' }}>Select supplier...</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Order notes..." value="{{ old('notes') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Line Items</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">+ Add Item</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 250px;">Product *</th>
                        <th style="width: 140px;">Quantity *</th>
                        <th style="width: 160px;">Unit Price ($) *</th>
                        <th style="width: 140px;" class="text-end">Subtotal ($)</th>
                        <th style="width: 60px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                        <td class="text-end fw-bold fs-5 text-primary" id="grandTotalDisplay">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Save as Draft</button>
        </div>
    </div>
</form>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][product_id]" class="form-select product-select" required>
                <option value="" disabled selected>Select product...</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}">
                        {{ $product->sku }} - {{ $product->name }} (Stock: {{ $product->stock_quantity }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control quantity-input" min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" name="items[INDEX][unit_price]" class="form-control price-input" min="0.01" step="0.01" value="0.00" required>
        </td>
        <td class="text-end fw-bold row-subtotal pe-2">$0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">&times;</button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemsBody = document.getElementById('itemsBody');
    const template = document.getElementById('rowTemplate').innerHTML;
    const addRowBtn = document.getElementById('addRowBtn');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');
    let rowIndex = 0;

    function addRow() {
        const rowHtml = template.replace(/INDEX/g, rowIndex);
        itemsBody.insertAdjacentHTML('beforeend', rowHtml);
        const newRow = itemsBody.lastElementChild;
        rowIndex++;
        attachRowEvents(newRow);
        calculateTotals();
    }

    function attachRowEvents(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        productSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const defaultPrice = selected.getAttribute('data-price');
            if (defaultPrice) {
                priceInput.value = parseFloat(defaultPrice).toFixed(2);
            }
            calculateRowSubtotal(row);
        });

        quantityInput.addEventListener('input', function () {
            calculateRowSubtotal(row);
        });

        priceInput.addEventListener('input', function () {
            calculateRowSubtotal(row);
        });

        removeBtn.addEventListener('click', function () {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('At least one item is required.');
            }
        });
    }

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = Math.max(0, qty * price);
        row.querySelector('.row-subtotal').textContent = '$' + subtotal.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            grandTotal += Math.max(0, qty * price);
        });
        grandTotalDisplay.textContent = '$' + grandTotal.toFixed(2);
    }

    addRowBtn.addEventListener('click', addRow);
    addRow();
});
</script>
@endpush
