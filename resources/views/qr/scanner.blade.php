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

    {{-- Upload QR Image --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Upload QR Code Image</h3>
                <p class="text-xs text-gray-500">Select a saved QR code image to open the product page.</p>
            </div>
        </div>

        {{-- Drop zone --}}
        <label id="upload-dropzone"
               class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300 rounded-xl p-6 cursor-pointer hover:border-violet-400 hover:bg-violet-50 transition-colors group">
            <svg class="w-8 h-8 text-gray-400 group-hover:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            <span class="text-sm text-gray-500 group-hover:text-violet-600 transition-colors">
                Click to choose or drag &amp; drop a QR image
            </span>
            <span class="text-xs text-gray-400">PNG, JPG, SVG, WebP supported</span>
            <input type="file" id="qr-upload-input" accept="image/*" class="hidden">
        </label>

        {{-- Preview + result --}}
        <div id="upload-preview-wrap" class="hidden flex gap-4 items-start">
            <img id="upload-preview-img" src="" alt="QR preview"
                 class="w-20 h-20 object-contain rounded-lg border border-gray-200 bg-gray-50 shrink-0">
            <div class="flex-1 min-w-0">
                <p id="upload-result-text" class="text-sm text-gray-700 break-all"></p>
                <a id="upload-result-link" href="#" target="_blank"
                   class="mt-2 inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Open Product
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>

        <div id="upload-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700"></div>
        <div id="upload-loading" class="hidden text-center text-sm text-gray-500 py-2">
            <svg class="animate-spin inline w-4 h-4 mr-1 text-violet-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Decoding QR code…
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
{{-- jsQR: lightweight, synchronous QR decoder used for image uploads --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
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

// ── Upload QR Image ────────────────────────────────────────────────────────────
const uploadInput    = document.getElementById('qr-upload-input');
const uploadDropzone = document.getElementById('upload-dropzone');
const uploadLoading  = document.getElementById('upload-loading');
const uploadError    = document.getElementById('upload-error');
const uploadPreview  = document.getElementById('upload-preview-wrap');
const previewImg     = document.getElementById('upload-preview-img');
const resultText     = document.getElementById('upload-result-text');
const resultLink     = document.getElementById('upload-result-link');

// Drag-and-drop support on the label
uploadDropzone.addEventListener('dragover', e => { e.preventDefault(); uploadDropzone.classList.add('border-violet-500', 'bg-violet-50'); });
uploadDropzone.addEventListener('dragleave', () => uploadDropzone.classList.remove('border-violet-500', 'bg-violet-50'));
uploadDropzone.addEventListener('drop', e => {
    e.preventDefault();
    uploadDropzone.classList.remove('border-violet-500', 'bg-violet-50');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) processUploadedFile(file);
});

uploadInput.addEventListener('change', function () {
    if (this.files[0]) processUploadedFile(this.files[0]);
});

function processUploadedFile(file) {
    uploadError.classList.add('hidden');
    uploadPreview.classList.add('hidden');
    uploadLoading.classList.remove('hidden');

    const objectUrl = URL.createObjectURL(file);
    const img = new Image();

    img.onload = function () {
        // Show preview immediately
        previewImg.src = objectUrl;

        // Render onto canvas — upscale small images so QR modules are sharp pixels.
        // Cap at 1200px on the longest side; larger than that gives no benefit.
        const TARGET  = 1200;
        const longest = Math.max(img.naturalWidth, img.naturalHeight);
        const scale   = longest >= TARGET ? 1 : TARGET / longest;
        const w = Math.round(img.naturalWidth  * scale);
        const h = Math.round(img.naturalHeight * scale);

        const canvas = document.createElement('canvas');
        canvas.width  = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = false; // hard edges on QR modules
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);

        URL.revokeObjectURL(objectUrl);

        // jsQR is synchronous and purpose-built for QR images — much faster than
        // ZXing's multi-format async pipeline for static image decoding.
        const imageData = ctx.getImageData(0, 0, w, h);
        const result    = jsQR(imageData.data, w, h, { inversionAttempts: 'dontInvert' });

        uploadLoading.classList.add('hidden');

        if (result) {
            handleUploadResult(result.data);
        } else {
            showUploadError('Could not detect a QR code in this image. Make sure the image is clear and unobstructed.');
        }
    };

    img.onerror = function () {
        uploadLoading.classList.add('hidden');
        URL.revokeObjectURL(objectUrl);
        showUploadError('Failed to load the image. Please try a different file.');
    };

    img.src = objectUrl;
}

function handleUploadResult(text) {
    let url = text;
    const skuMatch = text.match(/\/qr\/scan\/([^?#]+)/);
    if (skuMatch) {
        url = `/qr/scan/${skuMatch[1]}`;
    } else if (!text.startsWith('http')) {
        // Treat raw text as SKU
        url = `/qr/scan/${encodeURIComponent(text)}`;
    }

    resultText.textContent = `Detected: ${text}`;
    resultLink.href = url;
    uploadPreview.classList.remove('hidden');
}

function showUploadError(msg) {
    uploadError.textContent = msg;
    uploadError.classList.remove('hidden');
}
</script>
@endpush
