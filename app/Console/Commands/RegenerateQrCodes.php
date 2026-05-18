<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\QrCodeService;
use Illuminate\Console\Command;

class RegenerateQrCodes extends Command
{
    protected $signature   = 'qr:regenerate {--sku= : Regenerate only a specific SKU}';
    protected $description = 'Regenerate QR codes for all products using the current APP_URL';

    public function handle(QrCodeService $qrService): int
    {
        $this->info('Current APP_URL: ' . config('app.url'));
        $this->newLine();

        $query = Product::query();

        if ($sku = $this->option('sku')) {
            $query->where('sku', $sku);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->warn('No products found.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $success = 0;
        $failed  = 0;

        foreach ($products as $product) {
            try {
                $path = $qrService->generate($product);
                $product->update(['qr_code' => $path]);
                $success++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Failed for {$product->sku}: {$e->getMessage()}");
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$success} regenerated, {$failed} failed.");
        $this->newLine();
        $this->line("QR codes now encode: <comment>" . config('app.url') . "/qr/scan/{SKU}</comment>");

        return self::SUCCESS;
    }
}
