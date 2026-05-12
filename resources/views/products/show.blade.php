@extends('layouts.app')
@section('title', $product->name)

@section('header-actions')
    <a href="{{ route('products.edit', $product) }}"
       class="inline-flex items-center gap-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
@endsection

@section('content')
<div class="py-4 space-y-6" x-data="{ activeTab: 'overview' }">

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left: Product info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start gap-5">
                    <div class="w-20 h-20 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-900">{{ $product->name }}</h2>
                            @php $statusColor = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-600', 'discontinued' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor[$product->status] ?? '' }}">{{ ucfirst($product->status) }}</span>
                        </div>
                        <p class="text-gray-500 text-sm mt-1">{{ $product->description ?: 'No description.' }}</p>
                        <div class="flex flex-wrap gap-4 mt-3 text-sm">
                            <span class="text-gray-500">SKU: <span class="font-mono font-medium text-gray-800">{{ $product->sku }}</span></span>
                            @if($product->barcode)
                            <span class="text-gray-500">Barcode: <span class="font-mono font-medium text-gray-800">{{ $product->barcode }}</span></span>
                            @endif
                            @if($product->category)
                            <span class="text-gray-500">Category:
                                <span class="font-medium" style="color:{{ $product->category->color }}">{{ $product->category->name }}</span>
                            </span>
                            @endif
                            @if($product->location)
                            <span class="text-gray-500">Location: <span class="font-medium text-gray-800">{{ $product->location->name }}</span></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock cards --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-3xl font-bold {{ $product->is_out_of_stock ? 'text-red-600' : ($product->is_low_stock ? 'text-yellow-600' : 'text-gray-900') }}">
                        {{ $product->quantity }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Current Stock ({{ $product->unit }})</p>
                    @if($product->is_low_stock && !$product->is_out_of_stock)
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full mt-2 inline-block">Low Stock</span>
                    @elseif($product->is_out_of_stock)
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full mt-2 inline-block">Out of Stock</span>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Selling Price</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($product->quantity * $product->cost, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Stock Value (cost)</p>
                </div>
            </div>

            {{-- Quick stock actions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Quick Stock Actions</h3>
                <div class="grid sm:grid-cols-3 gap-4">
                    {{-- Stock In --}}
                    <form method="POST" action="{{ route('inventory.stock-in') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Qty to Add</label>
                            <input type="number" name="quantity" min="1" value="1" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <input type="text" name="reference" placeholder="Reference (optional)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            + Stock In
                        </button>
                    </form>

                    {{-- Stock Out --}}
                    <form method="POST" action="{{ route('inventory.stock-out') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Qty to Remove</label>
                            <input type="number" name="quantity" min="1" value="1" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <input type="text" name="reference" placeholder="Reference (optional)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            − Stock Out
                        </button>
                    </form>

                    {{-- Adjust --}}
                    <form method="POST" action="{{ route('inventory.adjust') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Set Exact Qty</label>
                            <input type="number" name="new_quantity" min="0" value="{{ $product->quantity }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <input type="text" name="notes" placeholder="Reason (optional)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            ⚖ Adjust
                        </button>
                    </form>
                </div>
            </div>

            {{-- Transaction history --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Transaction History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-3 text-left">Type</th>
                                <th class="px-5 py-3 text-right">Qty</th>
                                <th class="px-5 py-3 text-right">Before</th>
                                <th class="px-5 py-3 text-right">After</th>
                                <th class="px-5 py-3 text-left">Reference</th>
                                <th class="px-5 py-3 text-left">By</th>
                                <th class="px-5 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentTransactions as $tx)
                            @php $badge = ['in' => 'bg-green-100 text-green-700', 'out' => 'bg-red-100 text-red-700', 'adjustment' => 'bg-yellow-100 text-yellow-700', 'transfer' => 'bg-blue-100 text-blue-700']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $badge[$tx->type] ?? 'bg-gray-100 text-gray-700' }}">{{ $tx->type_label }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->quantity >= 0 ? '+' : '' }}{{ $tx->quantity }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-500">{{ $tx->quantity_before }}</td>
                                <td class="px-5 py-3 text-right text-gray-800 font-medium">{{ $tx->quantity_after }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $tx->reference ?: '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $tx->user->name ?? 'System' }}</td>
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: QR Code panel --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
                <h3 class="font-semibold text-gray-800 mb-4">QR Code</h3>
                @if($product->qr_code)
                    <div class="bg-gray-50 rounded-xl p-4 inline-block mb-4">
                        <img src="{{ $product->qr_code_url }}" alt="QR Code" class="w-48 h-48 mx-auto">
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Scan to view product details</p>
                    <div class="space-y-2">
                        <a href="{{ route('products.download-qr', $product) }}"
                           class="flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download SVG
                        </a>
                        <form method="POST" action="{{ route('products.regenerate-qr', $product) }}">
                            @csrf
                            <button type="submit" class="flex items-center justify-center gap-2 w-full border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Regenerate
                            </button>
                        </form>
                        <a href="{{ route('qr.bulk-print') }}?ids[]={{ $product->id }}"
                           class="flex items-center justify-center gap-2 w-full border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Label
                        </a>
                    </div>
                @else
                    <div class="py-8 text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <p class="text-sm">No QR code generated</p>
                        <form method="POST" action="{{ route('products.regenerate-qr', $product) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Generate QR Code
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Scan URL info --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 mb-2">Scan URL</p>
                <p class="text-xs font-mono text-gray-700 break-all bg-gray-50 rounded p-2">{{ route('qr.scan', $product->sku) }}</p>
            </div>

            {{-- Scan stats --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 mb-3">Scan Statistics</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Scans</span>
                        <span class="font-semibold text-gray-800">{{ $product->qrScans->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Last Scanned</span>
                        <span class="font-semibold text-gray-800">
                            {{ $product->qrScans->sortByDesc('created_at')->first()?->created_at->diffForHumans() ?? 'Never' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
