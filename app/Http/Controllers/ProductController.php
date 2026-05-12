<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService) {}

    public function index(Request $request): View
    {
        $query = Product::with(['category', 'location']);

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($category = $request->get('category')) {
            $query->where('category_id', $category);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($stock = $request->get('stock')) {
            match ($stock) {
                'low'  => $query->lowStock(),
                'out'  => $query->where('quantity', '<=', 0),
                default => null,
            };
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        return view('products.create', compact('categories', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'sku'          => 'required|string|max:100|unique:products',
            'barcode'      => 'nullable|string|max:100|unique:products',
            'description'  => 'nullable|string',
            'category_id'  => 'nullable|exists:categories,id',
            'location_id'  => 'nullable|exists:locations,id',
            'price'        => 'required|numeric|min:0',
            'cost'         => 'required|numeric|min:0',
            'quantity'     => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'unit'         => 'required|string|max:20',
            'status'       => 'required|in:active,inactive,discontinued',
            'image'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        // Generate QR code
        $qrPath = $this->qrCodeService->generate($product);
        $product->update(['qr_code' => $qrPath]);

        return redirect()->route('products.show', $product)
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'location', 'transactions.user', 'qrScans']);
        $recentTransactions = $product->transactions()->with('user')->latest()->limit(10)->get();
        return view('products.show', compact('product', 'recentTransactions'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'locations'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'sku'          => 'required|string|max:100|unique:products,sku,' . $product->id,
            'barcode'      => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'description'  => 'nullable|string',
            'category_id'  => 'nullable|exists:categories,id',
            'location_id'  => 'nullable|exists:locations,id',
            'price'        => 'required|numeric|min:0',
            'cost'         => 'required|numeric|min:0',
            'quantity'     => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'unit'         => 'required|string|max:20',
            'status'       => 'required|in:active,inactive,discontinued',
            'image'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', "Product updated successfully.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" deleted.");
    }

    public function regenerateQr(Product $product): RedirectResponse
    {
        if ($product->qr_code) {
            Storage::disk('public')->delete($product->qr_code);
        }
        $qrPath = $this->qrCodeService->generate($product);
        $product->update(['qr_code' => $qrPath]);

        return back()->with('success', 'QR code regenerated successfully.');
    }

    public function downloadQr(Product $product)
    {
        if (!$product->qr_code || !Storage::disk('public')->exists($product->qr_code)) {
            return back()->with('error', 'QR code not found.');
        }
        return Storage::disk('public')->download($product->qr_code, "qr-{$product->sku}.svg");
    }
}
