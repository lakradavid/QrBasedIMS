<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ── Public QR scan route (no auth required so physical QR codes always work) ──
Route::get('/qr/scan/{sku}', [QrController::class, 'scan'])->name('qr.scan');

// ── Auth routes (Breeze) ───────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/regenerate-qr', [ProductController::class, 'regenerateQr'])->name('products.regenerate-qr');
    Route::get('products/{product}/download-qr/{format?}', [ProductController::class, 'downloadQr'])->name('products.download-qr');

    // Inventory transactions
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    Route::post('inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');

    // QR quick actions
    Route::get('qr/scanner', [QrController::class, 'scanner'])->name('qr.scanner');
    Route::get('qr/quick-in/{product}', [QrController::class, 'quickStockIn'])->name('qr.quick-in');
    Route::get('qr/quick-out/{product}', [QrController::class, 'quickStockOut'])->name('qr.quick-out');
    Route::get('qr/bulk-print', [QrController::class, 'bulkPrint'])->name('qr.bulk-print');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/products', [ReportController::class, 'exportProducts'])->name('reports.export-products');
    Route::get('reports/export/transactions', [ReportController::class, 'exportTransactions'])->name('reports.export-transactions');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Locations
    Route::resource('locations', LocationController::class)->except(['show']);

    // Admin management (superadmin only)
    Route::middleware('superadmin')->group(function () {
        Route::get('admins', [AdminController::class, 'index'])->name('admin.index');
        Route::get('admins/create', [AdminController::class, 'create'])->name('admin.create');
        Route::post('admins', [AdminController::class, 'store'])->name('admin.store');
        Route::delete('admins/{user}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });
});
