<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QrScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QrController extends Controller
{
    /**
     * Handle a QR code scan — logs the scan and redirects to product page.
     */
    public function scan(Request $request, string $sku)
    {
        $product = Product::where('sku', $sku)->firstOrFail();

        QrScan::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'action'     => $request->get('action', 'view'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('products.show', $product);
    }

    /**
     * Quick stock-in form via QR scan.
     */
    public function quickStockIn(Product $product): View
    {
        return view('qr.quick-action', [
            'product' => $product,
            'action'  => 'in',
            'title'   => 'Quick Stock In',
        ]);
    }

    /**
     * Quick stock-out form via QR scan.
     */
    public function quickStockOut(Product $product): View
    {
        return view('qr.quick-action', [
            'product' => $product,
            'action'  => 'out',
            'title'   => 'Quick Stock Out',
        ]);
    }

    /**
     * QR Scanner page.
     */
    public function scanner(): \Illuminate\View\View
    {
        return view('qr.scanner');
    }

    /**
     * Bulk QR print page.
     */
    public function bulkPrint(Request $request): View
    {
        $ids      = $request->get('ids', []);
        $products = Product::whereIn('id', $ids)->get();
        return view('qr.bulk-print', compact('products'));
    }
}
