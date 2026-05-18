<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // Stock value by category
        $stockByCategory = Product::with('category')
            ->selectRaw('category_id, SUM(quantity) as total_qty, SUM(quantity * cost) as total_value, COUNT(*) as product_count')
            ->groupBy('category_id')
            ->get();

        // Transaction volume last 30 days
        $txVolume = InventoryTransaction::selectRaw("DATE(created_at) as date, type, SUM(ABS(quantity)) as total")
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        // Top 10 most moved products
        $topMoved = InventoryTransaction::selectRaw('product_id, SUM(ABS(quantity)) as moved')
            ->with('product')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('product_id')
            ->orderByDesc('moved')
            ->limit(10)
            ->get();

        // Low stock products
        $lowStock = Product::with(['category', 'location'])
            ->lowStock()
            ->orderByRaw('quantity - min_quantity ASC')
            ->get();

        // Out of stock
        $outOfStock = Product::with(['category', 'location'])
            ->where('quantity', '<=', 0)
            ->where('status', 'active')
            ->get();

        // Summary totals
        $summary = [
            'total_sku'        => Product::count(),
            'total_stock_value'=> Product::selectRaw('SUM(quantity * cost) as v')->value('v') ?? 0,
            'total_retail'     => Product::selectRaw('SUM(quantity * price) as v')->value('v') ?? 0,
            'tx_this_month'    => InventoryTransaction::whereMonth('created_at', now()->month)->count(),
        ];

        return view('reports.index', compact(
            'stockByCategory', 'txVolume', 'topMoved',
            'lowStock', 'outOfStock', 'summary'
        ));
    }

    public function exportProducts(Request $request): Response
    {
        $products = Product::with(['category', 'location'])->get();

        $csv  = "ID,Name,SKU,Barcode,Category,Location,Status,Quantity,Min Qty,Unit,Price,Cost,Stock Value\n";
        foreach ($products as $p) {
            $csv .= implode(',', [
                $p->id,
                '"' . str_replace('"', '""', $p->name) . '"',
                $p->sku,
                $p->barcode ?? '',
                $p->category->name ?? '',
                $p->location->code ?? '',
                $p->status,
                $p->quantity,
                $p->min_quantity,
                $p->unit,
                $p->price,
                $p->cost,
                round($p->quantity * $p->cost, 2),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function exportTransactions(Request $request): Response
    {
        $query = InventoryTransaction::with(['product', 'user', 'fromLocation', 'toLocation']);

        if ($from = $request->get('from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->get('to'))   $query->whereDate('created_at', '<=', $to);

        $transactions = $query->latest()->get();

        $csv  = "ID,Date,Type,Product,SKU,Quantity,Before,After,From,To,Reference,User,Notes\n";
        foreach ($transactions as $tx) {
            $csv .= implode(',', [
                $tx->id,
                $tx->created_at->format('Y-m-d H:i:s'),
                $tx->type,
                '"' . str_replace('"', '""', $tx->product->name ?? '') . '"',
                $tx->product->sku ?? '',
                $tx->quantity,
                $tx->quantity_before,
                $tx->quantity_after,
                $tx->fromLocation->code ?? '',
                $tx->toLocation->code ?? '',
                '"' . str_replace('"', '""', $tx->reference ?? '') . '"',
                $tx->user->name ?? 'System',
                '"' . str_replace('"', '""', $tx->notes ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
