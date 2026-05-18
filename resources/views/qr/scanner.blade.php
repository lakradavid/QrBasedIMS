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

    {{-- Upload QR Code Image --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Upload QR Code Image</h3>
                <p class="text-xs text-gray-500">Select a saved QR code image to open the product page.</p>
            </div>
        </div>

        {{-- Drop zone --}}
        <div id="upload-dropzone"
             class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/40 transition-colors"
             onclick="document.getElementById('qr-file-input').click()"
             ondragover="event.preventDefault(); this.classList.add('border-indigo-400','bg-indigo-50/40')"
             ondragleave="this.classList.remove('border-indigo-400','bg-indigo-50/40')"
             ondrop="handleFileDrop(event)">
            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <p class="text-sm text-gray-600 font-medium">Click to choose or drag &amp; drop a QR image</p>
            <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG, WebP supported</p>
            <input type="file" id="qr-file-input" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
        </div>

        {{-- Upload result --}}
        <div id="upload-result" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-green-700 font-semibold text-sm" id="upload-result-text"></p>
            <a id="upload-result-link" href="#"
               class="mt-2 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                View Product →
            </a>
        </div>
        <div id="upload-error" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 text-center text-sm text-red-700"></div>
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
{{-- ZXing QR/barcode decoder (camera scanning) --}}
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js"></script>
{{-- jsQR — reliable QR decoder for uploaded images --}}
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

        // listVideoInputDevices was removed as static in 0.20.x — call on instance
        // Fall back to environment-facing camera directly if the method is unavailable
        let deviceId = undefined;
        if (typeof codeReader.listVideoInputDevices === 'function') {
            const devices = await codeReader.listVideoInputDevices();
            if (!devices || !devices.length) throw new Error('No camera found.');
            const device = devices.find(d => /back|rear|environment/i.test(d.label)) || devices[0];
            deviceId = device.deviceId;
        }

        setStatus('Scanning…');
        scanning = true;

        await codeReader.decodeFromVideoDevice(deviceId, 'scanner-video', (result, err) => {
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

function handleFileDrop(event) {
    event.preventDefault();
    document.getElementById('upload-dropzone').classList.remove('border-indigo-400', 'bg-indigo-50/40');
    const file = event.dataTransfer.files[0];
    if (file) decodeImageFile(file);
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) decodeImageFile(file);
    // Reset so same file can be re-selected
    event.target.value = '';
}

function decodeImageFile(file) {
    document.getElementById('upload-result').classList.add('hidden');
    document.getElementById('upload-error').classList.add('hidden');

    if (!file.type.startsWith('image/')) {
        showUploadError('Please select a valid image file.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            // Try multiple canvas sizes for best decode accuracy
            const sizes = [
                { w: img.naturalWidth,      h: img.naturalHeight },       // original
                { w: img.naturalWidth * 2,  h: img.naturalHeight * 2 },   // 2x upscale
                { w: 800,                   h: 800 },                      // fixed 800px
            ];

            for (const size of sizes) {
                const canvas = document.createElement('canvas');
                canvas.width  = size.w;
                canvas.height = size.h;
                const ctx = canvas.getContext('2d');
                // White background (helps with transparent PNGs)
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'attemptBoth',
                });

                if (code) {
                    handleUploadResult(code.data);
                    return;
                }
            }

            showUploadError('No QR code found in this image. Make sure the QR code is clearly visible and try again.');
        };
        img.onerror = () => showUploadError('Could not load the image file.');
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function handleUploadResult(text) {
    let url;
    const skuMatch = text.match(/\/qr\/scan\/([^?#]+)/);
    if (skuMatch) {
        url = `/qr/scan/${skuMatch[1]}`;
    } else {
        url = `/qr/scan/${encodeURIComponent(text)}`;
    }

    document.getElementById('upload-result-text').textContent = `Detected: ${text}`;
    document.getElementById('upload-result-link').href = url;
    document.getElementById('upload-result').classList.remove('hidden');

    setTimeout(() => { window.location.href = url; }, 1500);
}

function showUploadError(msg) {
    const el = document.getElementById('upload-error');
    el.textContent = msg;
    el.classList.remove('hidden');
}
</script>
@endpush
