@extends('layouts.app')
@section('title', 'Dashboard')

@section('header-actions')
    <a href="{{ route('products.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
@endsection

@section('content')
<div class="py-4 space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @php
        $statCards = [
            ['label' => 'Total Products',  'value' => number_format($stats['total_products']),  'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'indigo'],
            ['label' => 'Active',          'value' => number_format($stats['active_products']),  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                  'color' => 'green'],
            ['label' => 'Low Stock',       'value' => number_format($stats['low_stock']),        'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'yellow'],
            ['label' => 'Out of Stock',    'value' => number_format($stats['out_of_stock']),     'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red'],
            ['label' => 'Categories',      'value' => number_format($stats['total_categories']), 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color' => 'purple'],
            ['label' => 'Inventory Value', 'value' => '$' . number_format($stats['total_value'], 2), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 8v1m0 0c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
        ];
        $colorMap = [
            'indigo' => 'bg-indigo-50 text-indigo-600',
            'green'  => 'bg-green-50 text-green-600',
            'yellow' => 'bg-yellow-50 text-yellow-600',
            'red'    => 'bg-red-50 text-red-600',
            'purple' => 'bg-purple-50 text-purple-600',
            'blue'   => 'bg-blue-50 text-blue-600',
        ];
        @endphp

        @foreach($statCards as $card)
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-3">
            <div class="w-10 h-10 rounded-lg {{ $colorMap[$card['color']] }} flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Recent Transactions --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Recent Transactions</h2>
                <a href="{{ route('inventory.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentTransactions as $tx)
                <div class="flex items-center gap-4 px-5 py-3">
                    @php
                    $badge = ['in' => 'bg-green-100 text-green-700', 'out' => 'bg-red-100 text-red-700', 'adjustment' => 'bg-yellow-100 text-yellow-700', 'transfer' => 'bg-blue-100 text-blue-700'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badge[$tx->type] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $tx->type_label }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $tx->product->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $tx->created_at->diffForHumans() }} · {{ $tx->user->name ?? 'System' }}</p>
                    </div>
                    <span class="text-sm font-semibold {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->quantity >= 0 ? '+' : '' }}{{ $tx->quantity }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No transactions yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Low Stock
                </h2>
                <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockProducts as $product)
                <a href="{{ route('products.show', $product) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        @if($product->image)
                            <img src="{{ $product->image_url }}" class="w-8 h-8 rounded-lg object-cover" alt="">
                        @else
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->sku }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $product->quantity <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                        {{ $product->quantity }}
                    </span>
                </a>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">All stock levels are healthy.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent QR Scans --}}
    @if($recentScans->count())
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Recent QR Scans</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Action</th>
                        <th class="px-5 py-3 text-left">User</th>
                        <th class="px-5 py-3 text-left">IP</th>
                        <th class="px-5 py-3 text-left">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentScans as $scan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $scan->product->name ?? '—' }}</td>
                        <td class="px-5 py-3"><span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs">{{ $scan->action }}</span></td>
                        <td class="px-5 py-3 text-gray-500">{{ $scan->user->name ?? 'Guest' }}</td>
                        <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $scan->ip_address }}</td>
                        <td class="px-5 py-3 text-gray-400">{{ $scan->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
