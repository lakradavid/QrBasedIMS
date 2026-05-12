@extends('layouts.app')
@section('title', $title)

@section('content')
<div class="py-4 max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        {{-- Product info --}}
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden">
                @if($product->image)
                    <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                @endif
            </div>
            <div>
                <h2 class="font-bold text-gray-900">{{ $product->name }}</h2>
                <p class="text-sm text-gray-500 font-mono">{{ $product->sku }}</p>
                <p class="text-sm text-gray-500">Current stock: <strong class="{{ $product->is_out_of_stock ? 'text-red-600' : 'text-gray-800' }}">{{ $product->quantity }} {{ $product->unit }}</strong></p>
            </div>
        </div>

        @if($action === 'in')
        <form method="POST" action="{{ route('inventory.stock-in') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Add <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" value="1" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-3 py-3 text-lg font-bold text-center focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                <input type="text" name="reference" placeholder="PO number, invoice…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl text-base font-semibold transition-colors">
                ✓ Confirm Stock In
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('inventory.stock-out') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Remove <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" max="{{ $product->quantity }}" value="1" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-3 py-3 text-lg font-bold text-center focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                <input type="text" name="reference" placeholder="Order number, reason…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl text-base font-semibold transition-colors">
                ✓ Confirm Stock Out
            </button>
        </form>
        @endif

        <a href="{{ route('products.show', $product) }}" class="block text-center text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            ← Back to product
        </a>
    </div>
</div>
@endsection
