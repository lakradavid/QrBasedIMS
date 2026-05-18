<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\QrScan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_products'   => Product::count(),
            'active_products'  => Product::active()->count(),
            'low_stock'        => Product::lowStock()->count(),
            'out_of_stock'     => Product::where('quantity', '<=', 0)->count(),
            'total_categories' => Category::count(),
            'total_value'      => Product::selectRaw('SUM(quantity * cost) as val')->value('val') ?? 0,
        ];

        $recentTransactions = InventoryTransaction::with(['product', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->active()
            ->limit(8)
            ->get();

        $recentScans = QrScan::with(['product', 'user'])
            ->latest()
            ->limit(8)
            ->get();

        // Chart data: transactions per day for last 7 days
        $chartData = InventoryTransaction::selectRaw("DATE(created_at) as date, type, SUM(ABS(quantity)) as total")
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('dashboard', compact('stats', 'recentTransactions', 'lowStockProducts', 'recentScans', 'chartData'));
    }
}
