<?php

namespace App\Services;

use App\Models\Product;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR code SVG for a product and store it.
     * Returns the storage path (relative to public disk).
     */
    public function generate(Product $product): string
    {
        // Always use APP_URL so the encoded link works after deployment.
        // Change APP_URL in .env to your live domain before deploying,
        // then run: php artisan qr:regenerate
        $url = rtrim(config('app.url'), '/') . '/qr/scan/' . urlencode($product->sku);

        $result = Builder::create()
            ->writer(new SvgWriter())
            ->writerOptions([])
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $directory = 'qrcodes';
        $filename  = "qr-{$product->sku}.svg";
        $path      = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $result->getString());

        return $path;
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
