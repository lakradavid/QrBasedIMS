<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryTransaction::with(['product', 'user', 'fromLocation', 'toLocation']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($product = $request->get('product')) {
            $query->where('product_id', $product);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();
        $products     = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $locations    = Location::orderBy('name')->get(['id', 'name', 'code']);

        // Totals for the summary bar (across the filtered set, not just current page)
        $totalsQuery = InventoryTransaction::query();
        if ($type    = $request->get('type'))    $totalsQuery->where('type', $type);
        if ($product = $request->get('product')) $totalsQuery->where('product_id', $product);
        if ($from    = $request->get('from'))    $totalsQuery->whereDate('created_at', '>=', $from);
        if ($to      = $request->get('to'))      $totalsQuery->whereDate('created_at', '<=', $to);

        $rawTotals = $totalsQuery->selectRaw("type, SUM(ABS(quantity)) as total")
            ->groupBy('type')->pluck('total', 'type');

        $totals = [
            'in'         => $rawTotals['in']         ?? 0,
            'out'        => $rawTotals['out']        ?? 0,
            'adjustment' => $rawTotals['adjustment'] ?? 0,
            'transfer'   => $rawTotals['transfer']   ?? 0,
        ];

        return view('inventory.index', compact('transactions', 'products', 'locations', 'totals'));
    }

    public function stockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $before  = $product->quantity;
        $after   = $before + $validated['quantity'];

        $product->update(['quantity' => $after]);

        InventoryTransaction::create([
            'product_id'      => $product->id,
            'user_id'         => Auth::id(),
            'type'            => 'in',
            'quantity'        => $validated['quantity'],
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'to_location_id'  => $validated['location_id'] ?? $product->location_id,
            'reference'       => $validated['reference'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Added {$validated['quantity']} {$product->unit} to {$product->name}.");
    }

    public function stockOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock. Available: {$product->quantity} {$product->unit}."]);
        }

        $before = $product->quantity;
        $after  = $before - $validated['quantity'];

        $product->update(['quantity' => $after]);

        InventoryTransaction::create([
            'product_id'        => $product->id,
            'user_id'           => Auth::id(),
            'type'              => 'out',
            'quantity'          => -$validated['quantity'],
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'from_location_id'  => $product->location_id,
            'reference'         => $validated['reference'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Removed {$validated['quantity']} {$product->unit} from {$product->name}.");
    }

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $before  = $product->quantity;
        $after   = $validated['new_quantity'];
        $diff    = $after - $before;

        $product->update(['quantity' => $after]);

        InventoryTransaction::create([
            'product_id'      => $product->id,
            'user_id'         => Auth::id(),
            'type'            => 'adjustment',
            'quantity'        => $diff,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'notes'           => $validated['notes'] ?? "Manual adjustment from {$before} to {$after}",
        ]);

        return back()->with('success', "Stock adjusted to {$after} {$product->unit} for {$product->name}.");
    }

    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id'   => 'required|exists:locations,id|different:from_location_id',
            'notes'            => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock."]);
        }

        $before = $product->quantity;

        // Update product location to destination
        $product->update(['location_id' => $validated['to_location_id']]);

        InventoryTransaction::create([
            'product_id'       => $product->id,
            'user_id'          => Auth::id(),
            'type'             => 'transfer',
            'quantity'         => $validated['quantity'],
            'quantity_before'  => $before,
            'quantity_after'   => $before,
            'from_location_id' => $validated['from_location_id'],
            'to_location_id'   => $validated['to_location_id'],
            'notes'            => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Transfer recorded successfully.");
    }
}
