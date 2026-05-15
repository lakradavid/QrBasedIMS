@extends('layouts.app')
@section('title', 'Inventory Transactions')

@section('header-actions')
    <button onclick="document.getElementById('modal-transfer').classList.remove('hidden')"
            class="inline-flex items-center gap-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        Transfer Stock
    </button>
    <a href="{{ route('reports.index') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Reports
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="in"         {{ request('type') == 'in'         ? 'selected' : '' }}>Stock In</option>
                <option value="out"        {{ request('type') == 'out'        ? 'selected' : '' }}>Stock Out</option>
                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="transfer"   {{ request('type') == 'transfer'   ? 'selected' : '' }}>Transfer</option>
            </select>
            <select name="product" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->sku }})
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['type','product','from','to']))
                <a href="{{ route('inventory.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Summary bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $summary = [
                ['label' => 'Stock In',    'value' => $totals['in'],         'color' => 'text-green-600',  'bg' => 'bg-green-50  border-green-200'],
                ['label' => 'Stock Out',   'value' => abs($totals['out']),   'color' => 'text-red-600',    'bg' => 'bg-red-50    border-red-200'],
                ['label' => 'Adjustments', 'value' => $totals['adjustment'], 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50 border-yellow-200'],
                ['label' => 'Transfers',   'value' => $totals['transfer'],   'color' => 'text-blue-600',   'bg' => 'bg-blue-50   border-blue-200'],
            ];
        @endphp
        @foreach($summary as $s)
        <div class="rounded-xl border {{ $s['bg'] }} px-4 py-3 flex items-center justify-between">
            <span class="text-xs font-medium text-gray-600">{{ $s['label'] }}</span>
            <span class="text-lg font-bold {{ $s['color'] }}">{{ number_format($s['value']) }}</span>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Before</th>
                        <th class="px-5 py-3 text-right">After</th>
                        <th class="px-5 py-3 text-left">From → To</th>
                        <th class="px-5 py-3 text-left">Reference</th>
                        <th class="px-5 py-3 text-left">By</th>
                        <th class="px-5 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $tx)
                    @php
                        $badge = [
                            'in'         => 'bg-green-100 text-green-700',
                            'out'        => 'bg-red-100 text-red-700',
                            'adjustment' => 'bg-yellow-100 text-yellow-700',
                            'transfer'   => 'bg-blue-100 text-blue-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $badge[$tx->type] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $tx->type_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('products.show', $tx->product) }}"
                               class="font-medium text-gray-800 hover:text-indigo-600">
                                {{ $tx->product->name ?? '—' }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono">{{ $tx->product->sku ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tx->quantity >= 0 ? '+' : '' }}{{ $tx->quantity }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ $tx->quantity_before }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-800">{{ $tx->quantity_after }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            @if($tx->fromLocation || $tx->toLocation)
                                {{ $tx->fromLocation->code ?? '—' }} → {{ $tx->toLocation->code ?? '—' }}
                            @else —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $tx->reference ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $tx->user->name ?? 'System' }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ $tx->created_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Transfer Modal --}}
<div id="modal-transfer" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('modal-transfer').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg">Transfer Stock</h3>
            <button onclick="document.getElementById('modal-transfer').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('inventory.transfer') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                <select name="product_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select product…</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" value="1" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Location <span class="text-red-500">*</span></label>
                    <select name="from_location_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select…</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Location <span class="text-red-500">*</span></label>
                    <select name="to_location_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select…</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                          placeholder="Optional reason for transfer…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Confirm Transfer
                </button>
                <button type="button"
                        onclick="document.getElementById('modal-transfer').classList.add('hidden')"
                        class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
