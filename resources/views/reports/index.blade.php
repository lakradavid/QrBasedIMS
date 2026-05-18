@extends('layouts.app')
@section('title', 'Reports & Analytics')

@section('header-actions')
    <a href="{{ route('reports.export-products') }}"
       class="inline-flex items-center gap-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export Products CSV
    </a>
    <a href="{{ route('reports.export-transactions') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export Transactions CSV
    </a>
@endsection

@section('content')
<div class="py-4 space-y-6">

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $kpis = [
            ['label' => 'Total SKUs',         'value' => number_format($summary['total_sku']),                    'sub' => 'products in system',    'color' => 'indigo'],
            ['label' => 'Stock Value (Cost)',  'value' => '₹' . number_format($summary['total_stock_value'], 2),  'sub' => 'at cost price',         'color' => 'green'],
            ['label' => 'Retail Value',        'value' => '₹' . number_format($summary['total_retail'], 2),       'sub' => 'at selling price',      'color' => 'blue'],
            ['label' => 'Transactions (MTD)',  'value' => number_format($summary['tx_this_month']),               'sub' => 'this calendar month',   'color' => 'purple'],
        ];
        $kpiColor = ['indigo'=>'bg-indigo-50 text-indigo-600','green'=>'bg-green-50 text-green-600','blue'=>'bg-blue-50 text-blue-600','purple'=>'bg-purple-50 text-purple-600'];
        @endphp
        @foreach($kpis as $k)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="w-10 h-10 rounded-lg {{ $kpiColor[$k['color']] }} flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $k['value'] }}</p>
            <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $k['label'] }}</p>
            <p class="text-xs text-gray-400">{{ $k['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Transaction Volume Chart (last 30 days) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Transaction Volume — Last 30 Days</h2>
        <canvas id="txChart" height="80"></canvas>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Stock by Category --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Stock Value by Category</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Category</th>
                            <th class="px-5 py-3 text-right">Products</th>
                            <th class="px-5 py-3 text-right">Total Qty</th>
                            <th class="px-5 py-3 text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stockByCategory as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                @if($row->category)
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $row->category->color }}"></span>
                                        {{ $row->category->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Uncategorised</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ $row->product_count }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800">{{ number_format($row->total_qty) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">₹{{ number_format($row->total_value, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Moved Products --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Top 10 Most Moved (30 days)</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topMoved as $i => $row)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $row->product->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $row->product->sku ?? '' }}</p>
                    </div>
                    <span class="text-sm font-bold text-indigo-600">{{ number_format($row->moved) }} units</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No movement data yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Low Stock & Out of Stock --}}
    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Low Stock --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                    Low Stock ({{ $lowStock->count() }})
                </h2>
                <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Product</th>
                            <th class="px-5 py-3 text-right">Stock</th>
                            <th class="px-5 py-3 text-right">Min</th>
                            <th class="px-5 py-3 text-right">Deficit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lowStock->take(8) as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('products.show', $p) }}" class="font-medium text-gray-800 hover:text-indigo-600">{{ $p->name }}</a>
                                <p class="text-xs text-gray-400">{{ $p->category->name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-yellow-600">{{ $p->quantity }}</td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $p->min_quantity }}</td>
                            <td class="px-5 py-3 text-right text-red-600 font-semibold">{{ $p->min_quantity - $p->quantity }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400 text-sm">All stock levels healthy ✓</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Out of Stock --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    Out of Stock ({{ $outOfStock->count() }})
                </h2>
                <a href="{{ route('products.index', ['stock' => 'out']) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($outOfStock as $p)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $p) }}" class="text-sm font-medium text-gray-800 hover:text-indigo-600 truncate block">{{ $p->name }}</a>
                        <p class="text-xs text-gray-400">{{ $p->sku }} · {{ $p->location->code ?? 'No location' }}</p>
                    </div>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Out</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No out-of-stock items ✓</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const raw = @json($txVolume);

    // Build date labels for last 30 days
    const labels = [];
    for (let i = 29; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        labels.push(d.toISOString().slice(0, 10));
    }

    const types = { in: [], out: [], adjustment: [], transfer: [] };
    const byDate = {};
    raw.forEach(r => {
        if (!byDate[r.date]) byDate[r.date] = {};
        byDate[r.date][r.type] = r.total;
    });

    labels.forEach(d => {
        types.in.push(byDate[d]?.in || 0);
        types.out.push(byDate[d]?.out || 0);
        types.adjustment.push(byDate[d]?.adjustment || 0);
        types.transfer.push(byDate[d]?.transfer || 0);
    });

    new Chart(document.getElementById('txChart'), {
        type: 'bar',
        data: {
            labels: labels.map(d => {
                const [, m, day] = d.split('-');
                return `${day}/${m}`;
            }),
            datasets: [
                { label: 'Stock In',    data: types.in,         backgroundColor: '#86efac' },
                { label: 'Stock Out',   data: types.out,        backgroundColor: '#fca5a5' },
                { label: 'Adjustment',  data: types.adjustment, backgroundColor: '#fde68a' },
                { label: 'Transfer',    data: types.transfer,   backgroundColor: '#93c5fd' },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
@endpush
