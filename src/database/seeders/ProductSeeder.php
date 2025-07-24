<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductTransaction;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil semua ID client
        $clientIds = Client::pluck('id');

        if ($clientIds->isEmpty()) {
            $this->command->warn('No clients found. Please insert clients first.');
            return;
        }

        foreach (range(1, 20) as $i) {
            // Buat 1 produk
            $product = Product::create([
                'client_id'   => $clientIds->random(),
                'name'        => $faker->word . ' ' . strtoupper($faker->bothify('##')),
                'description' => $faker->sentence(),
                'price'       => $faker->numberBetween(10000, 150000),
                'image'       => null,
                'category'    => $faker->randomElement(['kemeja', 'kaos']),
                'stock'       => 0, // nanti akan diisi lewat transaksi
            ]);

            // Tambah stok awal (produk masuk)
            $stokMasuk = $faker->numberBetween(30, 100);

            ProductTransaction::create([
                'product_id' => $product->id,
                'type'       => 'in',
                'quantity'   => $stokMasuk,
                'note'       => 'Stok awal',
            ]);

            $product->increment('stock', $stokMasuk);

            // Simulasi penjualan (stok keluar)
            $stokKeluar = $faker->numberBetween(0, $stokMasuk - 5); // pastikan tidak negatif

            if ($stokKeluar > 0) {
                ProductTransaction::create([
                    'product_id' => $product->id,
                    'type'       => 'out',
                    'quantity'   => $stokKeluar,
                    'note'       => 'Penjualan awal',
                ]);

                $product->decrement('stock', $stokKeluar);
            }
        }
    }
}
