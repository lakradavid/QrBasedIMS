<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin user
        $user = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
            ]
        );
        // Ensure existing user is superadmin
        $user->update(['role' => 'superadmin']);

        // Categories
        $categories = [
            ['name' => 'Electronics',    'color' => '#6366f1', 'description' => 'Electronic components and devices'],
            ['name' => 'Office Supplies', 'color' => '#f59e0b', 'description' => 'Stationery and office materials'],
            ['name' => 'Furniture',       'color' => '#10b981', 'description' => 'Office and warehouse furniture'],
            ['name' => 'Tools',           'color' => '#ef4444', 'description' => 'Hand tools and power tools'],
            ['name' => 'Packaging',       'color' => '#8b5cf6', 'description' => 'Boxes, tape, and packing materials'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // Locations
        $locations = [
            ['name' => 'Warehouse A - Shelf 1', 'code' => 'WH-A1', 'building' => 'Warehouse A', 'floor' => 'Ground', 'aisle' => 'Shelf 1'],
            ['name' => 'Warehouse A - Shelf 2', 'code' => 'WH-A2', 'building' => 'Warehouse A', 'floor' => 'Ground', 'aisle' => 'Shelf 2'],
            ['name' => 'Warehouse B - Rack 1',  'code' => 'WH-B1', 'building' => 'Warehouse B', 'floor' => 'Ground', 'aisle' => 'Rack 1'],
            ['name' => 'Office Storage',         'code' => 'OFF-S1', 'building' => 'Office',     'floor' => '1st',   'aisle' => 'Storage Room'],
            ['name' => 'Reception',              'code' => 'REC-01', 'building' => 'Office',     'floor' => 'Ground', 'aisle' => 'Reception'],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }

        // Sample products
        $electronics = Category::where('name', 'Electronics')->first();
        $office      = Category::where('name', 'Office Supplies')->first();
        $tools       = Category::where('name', 'Tools')->first();
        $loc1        = Location::where('code', 'WH-A1')->first();
        $loc2        = Location::where('code', 'WH-A2')->first();
        $loc4        = Location::where('code', 'OFF-S1')->first();

        $products = [
            ['name' => 'USB-C Hub 7-Port',      'sku' => 'ELEC-001', 'category_id' => $electronics->id, 'location_id' => $loc1->id, 'price' => 49.99,  'cost' => 22.00, 'quantity' => 45,  'min_quantity' => 10, 'unit' => 'pcs',  'status' => 'active'],
            ['name' => 'Wireless Keyboard',      'sku' => 'ELEC-002', 'category_id' => $electronics->id, 'location_id' => $loc1->id, 'price' => 79.99,  'cost' => 35.00, 'quantity' => 8,   'min_quantity' => 10, 'unit' => 'pcs',  'status' => 'active'],
            ['name' => '27" Monitor',            'sku' => 'ELEC-003', 'category_id' => $electronics->id, 'location_id' => $loc2->id, 'price' => 299.99, 'cost' => 180.00,'quantity' => 12,  'min_quantity' => 5,  'unit' => 'pcs',  'status' => 'active'],
            ['name' => 'A4 Paper Ream 500 Sheets','sku' => 'OFF-001', 'category_id' => $office->id,      'location_id' => $loc4->id, 'price' => 8.99,   'cost' => 4.50,  'quantity' => 200, 'min_quantity' => 50, 'unit' => 'ream', 'status' => 'active'],
            ['name' => 'Ballpoint Pens Box',     'sku' => 'OFF-002',  'category_id' => $office->id,      'location_id' => $loc4->id, 'price' => 5.99,   'cost' => 2.00,  'quantity' => 3,   'min_quantity' => 20, 'unit' => 'box',  'status' => 'active'],
            ['name' => 'Cordless Drill',         'sku' => 'TOOL-001', 'category_id' => $tools->id,       'location_id' => $loc2->id, 'price' => 129.99, 'cost' => 65.00, 'quantity' => 0,   'min_quantity' => 3,  'unit' => 'pcs',  'status' => 'active'],
            ['name' => 'Safety Gloves (L)',      'sku' => 'TOOL-002', 'category_id' => $tools->id,       'location_id' => $loc2->id, 'price' => 12.99,  'cost' => 5.00,  'quantity' => 60,  'min_quantity' => 15, 'unit' => 'pair', 'status' => 'active'],
        ];

        $qrService = app(QrCodeService::class);

        foreach ($products as $prod) {
            $product = Product::firstOrCreate(['sku' => $prod['sku']], $prod);
            if (!$product->qr_code) {
                $qrPath = $qrService->generate($product);
                $product->update(['qr_code' => $qrPath]);
            }
        }
    }
}
