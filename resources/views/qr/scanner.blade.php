@extends('layouts.app')
@section('title', 'QR Scanner')

@section('content')
<div class="py-4 max-w-lg mx-auto space-y-4">

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="text-center">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <h2 class="font-bold text-gray-900 text-lg">Scan a QR Code</h2>
            <p class="text-sm text-gray-500 mt-1">Point your camera at a product QR code to look it up instantly.</p>
        </div>

        {{-- Camera viewfinder --}}
        <div class="relative rounded-xl overflow-hidden bg-black aspect-square" id="scanner-container">
            <video id="scanner-video" class="w-full h-full object-cover" playsinline></video>
            {{-- Crosshair overlay --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-48 h-48 border-2 border-white/70 rounded-xl relative">
                    <span class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-indigo-400 rounded-tl-lg"></span>
                    <span class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-indigo-400 rounded-tr-lg"></span>
                    <span class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-indigo-400 rounded-bl-lg"></span>
                    <span class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-indigo-400 rounded-br-lg"></span>
                </div>
            </div>
            {{-- Status overlay --}}
            <div id="scanner-status" class="absolute bottom-3 left-0 right-0 text-center">
                <span class="bg-black/60 text-white text-xs px-3 py-1.5 rounded-full">Starting camera…</span>
            </div>
        </div>

        <div id="scanner-result" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-green-700 font-semibold text-sm" id="result-text">Scanned!</p>
            <a id="result-link" href="#" class="mt-2 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                View Product →
            </a>
        </div>

        <div id="scanner-error" class="hidden bg-red-50 border border-red-200 rounded-xl p-4 text-center text-sm text-red-700"></div>

        <div class="flex gap-3">
            <button id="btn-start" onclick="startScanner()"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Start Camera
            </button>
            <button id="btn-stop" onclick="stopScanner()" disabled
                    class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Stop
            </button>
        </div>
    </div>

    {{-- Manual SKU lookup --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Manual SKU Lookup</h3>
        <form id="sku-form" class="flex gap-3">
            <input type="text" id="sku-input" placeholder="Enter SKU or scan barcode…"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Look Up
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
{{-- ZXing QR/barcode decoder --}}
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js"></script>
<script>
let codeReader = null;
let scanning   = false;

function setStatus(msg) {
    document.getElementById('scanner-status').innerHTML =
        `<span class="bg-black/60 text-white text-xs px-3 py-1.5 rounded-full">${msg}</span>`;
}

async function startScanner() {
    document.getElementById('scanner-result').classList.add('hidden');
    document.getElementById('scanner-error').classList.add('hidden');
    document.getElementById('btn-start').disabled = true;
    document.getElementById('btn-stop').disabled  = false;

    try {
        codeReader = new ZXing.BrowserMultiFormatReader();
        const devices = await ZXing.BrowserCodeReader.listVideoInputDevices();

        if (!devices.length) throw new Error('No camera found.');

        // Prefer back camera on mobile
        const device = devices.find(d => /back|rear|environment/i.test(d.label)) || devices[0];
        setStatus('Scanning…');
        scanning = true;

        await codeReader.decodeFromVideoDevice(device.deviceId, 'scanner-video', (result, err) => {
            if (result && scanning) {
                scanning = false;
                const text = result.getText();
                handleScanResult(text);
            }
        });
    } catch (e) {
        showError(e.message || 'Camera access denied or unavailable.');
        document.getElementById('btn-start').disabled = false;
        document.getElementById('btn-stop').disabled  = true;
    }
}

function stopScanner() {
    scanning = false;
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
    setStatus('Camera stopped.');
    document.getElementById('btn-start').disabled = false;
    document.getElementById('btn-stop').disabled  = true;
}

function handleScanResult(text) {
    stopScanner();
    setStatus('QR code detected!');

    // If it's a full URL from our system, extract the SKU
    let url = text;
    const skuMatch = text.match(/\/qr\/scan\/([^?#]+)/);
    if (skuMatch) {
        url = `/qr/scan/${skuMatch[1]}`;
    } else {
        // Treat raw text as SKU
        url = `/qr/scan/${encodeURIComponent(text)}`;
    }

    const resultBox  = document.getElementById('scanner-result');
    const resultText = document.getElementById('result-text');
    const resultLink = document.getElementById('result-link');

    resultText.textContent = `Detected: ${text}`;
    resultLink.href = url;
    resultBox.classList.remove('hidden');

    // Auto-navigate after 1.5s
    setTimeout(() => { window.location.href = url; }, 1500);
}

function showError(msg) {
    const el = document.getElementById('scanner-error');
    el.textContent = msg;
    el.classList.remove('hidden');
    setStatus('Error.');
}

// Manual SKU form
document.getElementById('sku-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const sku = document.getElementById('sku-input').value.trim();
    if (sku) window.location.href = `/qr/scan/${encodeURIComponent(sku)}`;
});
</script>
@endpush
