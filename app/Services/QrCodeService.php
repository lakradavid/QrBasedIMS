<?php

namespace App\Services;

use App\Models\Product;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate QR code files (SVG for display, PNG for download) and store them.
     * Returns the storage path of the SVG (relative to public disk).
     */
    public function generate(Product $product): string
    {
        $url = rtrim(config('app.url'), '/') . '/qr/scan/' . urlencode($product->sku);

        // ── SVG (used for display in the UI) ──────────────────────────────────
        $svgResult = Builder::create()
            ->writer(new SvgWriter())
            ->writerOptions([])
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $svgPath = "qrcodes/qr-{$product->sku}.svg";
        Storage::disk('public')->put($svgPath, $svgResult->getString());

        // ── PNG (used for download) ────────────────────────────────────────────
        $pngResult = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(600)   // higher resolution for print quality
            ->margin(20)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $pngPath = "qrcodes/qr-{$product->sku}.png";
        Storage::disk('public')->put($pngPath, $pngResult->getString());

        return $svgPath;
    }

    /**
     * Generate a QR code as a data URI (for inline embedding).
     */
    public function dataUri(Product $product): string
    {
        $url = rtrim(config('app.url'), '/') . '/qr/scan/' . urlencode($product->sku);

        $result = Builder::create()
            ->writer(new SvgWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(5)
            ->build();

        return $result->getDataUri();
    }
}
