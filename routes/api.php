<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('api.inventory.index');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('api.inventory.low-stock');

    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('api.purchase-orders.store');
    Route::patch('/purchase-orders/{id}/status', [PurchaseOrderController::class, 'updateStatus'])->name('api.purchase-orders.update-status');

    Route::get('/reports/supplier-spend', [ReportController::class, 'supplierSpend'])->name('api.reports.supplier-spend');
});
