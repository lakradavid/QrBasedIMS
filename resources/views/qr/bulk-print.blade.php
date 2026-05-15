<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Codes — QR Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .qr-card { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <div class="no-print flex items-center justify-between mb-6 bg-white rounded-xl border border-gray-200 px-5 py-4">
        <div>
            <h1 class="font-bold text-gray-900">Print QR Labels</h1>
            <p class="text-sm text-gray-500">{{ $products->count() }} product(s) selected</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                🖨 Print
            </button>
            <a href="{{ url()->previous() }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                ← Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @forelse($products as $product)
        <div class="qr-card bg-white rounded-xl border border-gray-200 p-4 text-center">
            @if($product->qr_code)
                <img src="{{ $product->qr_code_url }}" alt="QR" class="w-32 h-32 mx-auto mb-2">
            @else
                <div class="w-32 h-32 mx-auto mb-2 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs">No QR</div>
            @endif
            <p class="font-bold text-gray-900 text-sm leading-tight">{{ $product->name }}</p>
            <p class="font-mono text-xs text-gray-500 mt-0.5">{{ $product->sku }}</p>
            @if($product->location)
                <p class="text-xs text-gray-400 mt-0.5">📍 {{ $product->location->code }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-0.5">Qty: {{ $product->quantity }} {{ $product->unit }}</p>
        </div>
        @empty
        <div class="col-span-4 text-center py-12 text-gray-400">No products selected.</div>
        @endforelse
    </div>
</body>
</html>
