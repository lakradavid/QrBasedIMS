@extends('layouts.app')
@section('title', 'Products')

@section('header-actions')
    <a href="{{ route('products.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4" x-data="{ selected: [], selectAll: false }">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, barcode…"
                   class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="category" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
            </select>
            <select name="stock" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Stock</option>
                <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Filter</button>
            @if(request()->hasAny(['search','category','status','stock']))
                <a href="{{ route('products.index') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Clear</a>
            @endif
        </div>
    </form>

    {{-- Bulk actions bar --}}
    <div x-show="selected.length > 0" x-cloak class="bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3 flex items-center gap-4">
        <span class="text-sm text-indigo-700 font-medium" x-text="selected.length + ' selected'"></span>
        <a :href="'{{ route('qr.bulk-print') }}?ids[]=' + selected.join('&ids[]=')"
           class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors">
            Print QR Codes
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" @change="selectAll = $event.target.checked; selected = selectAll ? {{ $products->pluck('id') }} : []"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Location</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">QR</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="{{ $product->id }}" x-model="selected"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $product->name }}</a>
                                    @if($product->barcode)
                                        <p class="text-xs text-gray-400">{{ $product->barcode }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $product->sku }}</td>
                        <td class="px-4 py-3">
                            @if($product->category)
                                <span class="inline-flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 rounded-full" style="background:{{ $product->category->color }}"></span>
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $product->location->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @php
                            $stockClass = match($product->stock_status) {
                                'out_of_stock' => 'text-red-600 font-bold',
                                'low_stock'    => 'text-yellow-600 font-semibold',
                                default        => 'text-gray-800',
                            };
                            @endphp
                            <span class="{{ $stockClass }}">{{ $product->quantity }}</span>
                            <span class="text-gray-400 text-xs"> {{ $product->unit }}</span>
                            @if($product->is_low_stock)
                                <span class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">Low</span>
                            @endif
                            @if($product->is_out_of_stock)
                                <span class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Out</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">${{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $statusColor = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-600', 'discontinued' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusColor[$product->status] ?? '' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product->qr_code)
                                <a href="{{ route('products.show', $product) }}" title="View QR">
                                    <img src="{{ $product->qr_code_url }}" class="w-8 h-8 mx-auto" alt="QR">
                                </a>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.show', $product) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            No products found.
                            <a href="{{ route('products.create') }}" class="text-indigo-600 hover:underline ml-1">Add one?</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
