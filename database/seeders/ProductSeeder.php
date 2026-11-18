<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Folder sumber gambar (ikut di repo).
     * Gambar diambil dari Wikimedia Commons (lisensi bebas / Creative Commons).
     */
    protected string $sourceDir = __DIR__ . '/images';

    /**
     * Base URL Wikimedia, dipakai sebagai cadangan jika file lokal tidak ada.
     */
    protected string $wiki = 'https://thumb.wikimedia.org/wikipedia/commons/thumb';

    public function run(): void
    {
        $disk = Storage::disk('public');

        // 1. Hapus produk & kategori lama beserta file gambarnya
        $disk->delete(Product::pluck('image')->filter()->all());
        $disk->delete(Category::pluck('image')->filter()->all());

        Product::query()->delete();   // relasi carts, ratings, transaction_details ikut terhapus (cascade)
        Category::query()->delete();

        $disk->deleteDirectory('products');

        // 2. Buat kategori & produk baru
        foreach ($this->data() as $categoryData) {
            $categorySlug = Str::slug($categoryData['name']);

            $category = Category::create([
                'name'  => $categoryData['name'],
                'slug'  => $categorySlug,
                'image' => $this->storeImage($categoryData['image'], $categoryData['url'], 'categories', $categorySlug),
            ]);

            foreach ($categoryData['products'] as $product) {
                $productSlug = Str::slug($product['title']);

                $category->products()->create([
                    'title'       => $product['title'],
                    'slug'        => $productSlug,
                    'description' => $product['description'],
                    'price'       => $product['price'],
                    'weight'      => $product['weight'],
                    'image'       => $this->storeImage($product['image'], $product['url'], 'products', $productSlug),
                ]);
            }

            $this->command?->info("Kategori {$category->name}: " . count($categoryData['products']) . ' produk');
        }
    }

    /**
     * Salin gambar ke storage/app/public/{folder} dan kembalikan path relatifnya
     * (format sama dengan hasil FileUpload Filament, mis. "products/nasi-goreng.jpg").
     */
    protected function storeImage(string $file, string $url, string $folder, string $slug): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $target    = "{$folder}/{$slug}.{$extension}";
        $local     = "{$this->sourceDir}/{$file}";

        if (is_file($local)) {
            $contents = file_get_contents($local);
        } else {
            // Cadangan: unduh langsung dari internet
            $response = Http::withHeaders(['User-Agent' => 'food-store-seeder/1.0'])
                ->timeout(30)
                ->retry(3, 1000)
                ->get($url);

            $response->throw();
            $contents = $response->body();
        }

        Storage::disk('public')->put($target, $contents);

        return $target;
    }

    protected function data(): array
    {
        $w = $this->wiki;

        return [
            [
                'name'  => 'Makanan Berat',
                'image' => 'categories/makanan-berat.jpg',
                'url'   => "$w/d/d6/Nasi_Padang_dishes_at_Simpang_Raya_Jam_Gadang_Restaurant_-_Guguk_Panjang%2C_Bukit_Tinggi%2C_West_Sumatra.jpg/960px-Nasi_Padang_dishes_at_Simpang_Raya_Jam_Gadang_Restaurant_-_Guguk_Panjang%2C_Bukit_Tinggi%2C_West_Sumatra.jpg",
                'products' => [
                    [
                        'title'       => 'Nasi Goreng Kampung',
                        'description' => 'Nasi goreng khas kampung dengan bumbu terasi, telur mata sapi, ayam suwir, kerupuk, dan acar segar.',
                        'price'       => 22000,
                        'weight'      => 400,
                        'image'       => 'products/nasi-goreng.jpg',
                        'url'         => "$w/c/c7/Nasi_Goreng_Kampung_%2811967588375%29.jpg/960px-Nasi_Goreng_Kampung_%2811967588375%29.jpg",
                    ],
                    [
                        'title'       => 'Mie Goreng Spesial',
                        'description' => 'Mie goreng dengan udang, ayam, sawi hijau, dan kol, dimasak dengan kecap manis dan bawang putih.',
                        'price'       => 20000,
                        'weight'      => 400,
                        'image'       => 'products/mie-goreng.jpg',
                        'url'         => "$w/f/f0/Mi_Goreng_GM.jpg/960px-Mi_Goreng_GM.jpg",
                    ],
                    [
                        'title'       => 'Rendang Daging Sapi',
                        'description' => 'Rendang daging sapi asli Padang yang dimasak berjam-jam dengan santan dan rempah pilihan hingga empuk dan meresap.',
                        'price'       => 35000,
                        'weight'      => 350,
                        'image'       => 'products/rendang.jpg',
                        'url'         => "$w/7/70/Rendang_daging_sapi_asli_Padang.JPG/960px-Rendang_daging_sapi_asli_Padang.JPG",
                    ],
                    [
                        'title'       => 'Ayam Geprek Sambal Bawang',
                        'description' => 'Ayam goreng tepung renyah yang digeprek dengan sambal bawang pedas, disajikan dengan nasi putih dan lalapan.',
                        'price'       => 18000,
                        'weight'      => 400,
                        'image'       => 'products/ayam-geprek.png',
                        'url'         => "$w/2/24/Ayam_geprek.png/960px-Ayam_geprek.png",
                    ],
                    [
                        'title'       => 'Nasi Padang Komplit',
                        'description' => 'Nasi putih dengan rendang, ayam pop, gulai nangka, daun singkong, dan sambal hijau khas Minang.',
                        'price'       => 30000,
                        'weight'      => 500,
                        'image'       => 'products/nasi-padang.jpg',
                        'url'         => "$w/4/4a/Nasi_Padang_Enak_Sedap.jpg/960px-Nasi_Padang_Enak_Sedap.jpg",
                    ],
                ],
            ],
            [
                'name'  => 'Minuman',
                'image' => 'categories/minuman.jpg',
                'url'   => "$w/5/5e/Es_kopi_susu_Pangkalpinang_22_Juni_2018.jpg/960px-Es_kopi_susu_Pangkalpinang_22_Juni_2018.jpg",
                'products' => [
                    [
                        'title'       => 'Es Teh Manis',
                        'description' => 'Teh melati seduh yang manis dan segar, disajikan dengan es batu.',
                        'price'       => 5000,
                        'weight'      => 350,
                        'image'       => 'products/es-teh-manis.jpg',
                        'url'         => "$w/7/7c/%C3%88s_T%C3%A8h_1.jpg/960px-%C3%88s_T%C3%A8h_1.jpg",
                    ],
                    [
                        'title'       => 'Es Jeruk Peras',
                        'description' => 'Perasan jeruk peras asli dengan sedikit gula dan es batu, kaya vitamin C.',
                        'price'       => 8000,
                        'weight'      => 350,
                        'image'       => 'products/es-jeruk.jpg',
                        'url'         => "$w/b/be/Es_Jeruk_Kampung.jpg/960px-Es_Jeruk_Kampung.jpg",
                    ],
                    [
                        'title'       => 'Es Kopi Susu Gula Aren',
                        'description' => 'Espresso robusta dengan susu segar dan gula aren, creamy dan tidak terlalu manis.',
                        'price'       => 18000,
                        'weight'      => 350,
                        'image'       => 'products/es-kopi-susu.jpg',
                        'url'         => "$w/0/05/Es_Kopi_Susu_Kekinian.jpg/960px-Es_Kopi_Susu_Kekinian.jpg",
                    ],
                    [
                        'title'       => 'Es Cendol',
                        'description' => 'Cendol hijau pandan dengan santan gurih dan gula merah cair, disajikan dingin.',
                        'price'       => 10000,
                        'weight'      => 350,
                        'image'       => 'products/es-cendol.jpg',
                        'url'         => "$w/1/17/Es_cendol_-_Wajan.jpg/960px-Es_cendol_-_Wajan.jpg",
                    ],
                    [
                        'title'       => 'Es Teler',
                        'description' => 'Campuran alpukat, kelapa muda, nangka, dan susu kental manis di atas serutan es.',
                        'price'       => 15000,
                        'weight'      => 400,
                        'image'       => 'products/es-teler.jpg',
                        'url'         => "$w/b/bd/Es_teler.jpg/960px-Es_teler.jpg",
                    ],
                ],
            ],
            [
                'name'  => 'Camilan',
                'image' => 'categories/camilan.jpg',
                'url'   => "$w/c/c1/Kue_Martabak_Manis.jpg/960px-Kue_Martabak_Manis.jpg",
                'products' => [
                    [
                        'title'       => 'Pisang Goreng Crispy',
                        'description' => 'Pisang kepok goreng dengan balutan tepung renyah, cocok ditemani teh atau kopi.',
                        'price'       => 10000,
                        'weight'      => 250,
                        'image'       => 'products/pisang-goreng.jpg',
                        'url'         => "$w/9/9f/Banana_Fritters_and_tea.jpg/960px-Banana_Fritters_and_tea.jpg",
                    ],
                    [
                        'title'       => 'Martabak Manis Coklat Keju',
                        'description' => 'Martabak manis tebal dan lembut dengan topping coklat meses, keju parut, dan susu kental manis.',
                        'price'       => 35000,
                        'weight'      => 600,
                        'image'       => 'products/martabak-manis.jpg',
                        'url'         => "$w/9/93/Martabak_manis_coklat_keju_khas_indonesia.jpg/960px-Martabak_manis_coklat_keju_khas_indonesia.jpg",
                    ],
                    [
                        'title'       => 'Martabak Telur',
                        'description' => 'Kulit martabak tipis renyah berisi telur bebek, daging cincang, dan daun bawang, disajikan dengan acar.',
                        'price'       => 30000,
                        'weight'      => 500,
                        'image'       => 'products/martabak-telur.jpg',
                        'url'         => "$w/a/a2/MartabakTelur.JPG/960px-MartabakTelur.JPG",
                    ],
                    [
                        'title'       => 'Kentang Goreng',
                        'description' => 'Kentang goreng renyah dengan taburan bumbu gurih, disajikan dengan saus sambal.',
                        'price'       => 15000,
                        'weight'      => 200,
                        'image'       => 'products/kentang-goreng.jpg',
                        'url'         => "$w/6/65/Kentang_Goreng_Neat.Co.jpg/960px-Kentang_Goreng_Neat.Co.jpg",
                    ],
                    [
                        'title'       => 'Gorengan Udang Tepung',
                        'description' => 'Paket gorengan berisi udang tepung, onion ring, dan jamur crispy dengan saus cocolan.',
                        'price'       => 25000,
                        'weight'      => 300,
                        'image'       => 'products/gorengan.jpg',
                        'url'         => "$w/4/47/Deep-fried_foods_with_dipping_sauce.jpg/960px-Deep-fried_foods_with_dipping_sauce.jpg",
                    ],
                ],
            ],
        ];
    }
}
