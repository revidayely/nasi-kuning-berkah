<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Seeder demo dilewati. Jangan memasukkan data contoh ke lingkungan produksi.');

            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'owner@warung.test'],
            [
                'name' => 'Pemilik Warung',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'kasir@warung.test'],
            [
                'name' => 'Kasir Warung',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        $paket = Category::query()->firstOrCreate(
            ['slug' => 'paket-nasi'],
            ['name' => 'Paket Nasi', 'description' => 'Paket hidangan nasi kuning komplit siap santap.']
        );

        $lauk = Category::query()->firstOrCreate(
            ['slug' => 'lauk-tambahan'],
            ['name' => 'Lauk Tambahan', 'description' => 'Pilihan lauk pauk lezat pendamping nasi kuning.']
        );

        $minuman = Category::query()->firstOrCreate(
            ['slug' => 'minuman'],
            ['name' => 'Minuman', 'description' => 'Minuman segar dingin dan hangat pelepas dahaga.']
        );

        $menus = [
            [
                'category_id' => $paket->id,
                'name' => 'Paket Komplit',
                'slug' => 'paket-komplit',
                'description' => 'Nasi kuning harum, ayam goreng gurih, telur balado, orek tempe renyah, perkedel, timun & sambal khas Berkah.',
                'price' => 25000,
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80',
                'stock' => 30,
                'min_stock' => 5,
            ],
            [
                'category_id' => $paket->id,
                'name' => 'Nasi Kuning Original',
                'slug' => 'nasi-kuning-original',
                'description' => 'Nasi kuning pulen bumbu rempah kunyit santan, serundeng kelapa sangrai, irisan mentimun segar, dan sambal pedas manis.',
                'price' => 10000,
                'image_url' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80',
                'stock' => 40,
                'min_stock' => 10,
            ],
            [
                'category_id' => $lauk->id,
                'name' => 'Ayam Goreng',
                'slug' => 'ayam-goreng',
                'description' => 'Ayam ungkep bumbu kuning lengkuas khas warung, digoreng garing di luar dan lembut gurih hingga ke tulang.',
                'price' => 12000,
                'image_url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80',
                'stock' => 25,
                'min_stock' => 5,
            ],
            [
                'category_id' => $lauk->id,
                'name' => 'Telur Balado',
                'slug' => 'telur-balado',
                'description' => 'Telur rebus digoreng berkulit berselimut tumisan sambal balado merah segar yang gurih pedas manis.',
                'price' => 8000,
                'image_url' => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=600&q=80',
                'stock' => 30,
                'min_stock' => 6,
            ],
            [
                'category_id' => $lauk->id,
                'name' => 'Sambal Goreng Ati',
                'slug' => 'sambal-goreng-ati',
                'description' => 'Ati ampela empuk dimasak perlahan dalam bumbu cabai merah gurih dengan potongan kentang dadu lembut.',
                'price' => 8000,
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80',
                'stock' => 20,
                'min_stock' => 4,
            ],
            [
                'category_id' => $lauk->id,
                'name' => 'Perkedel Kentang',
                'slug' => 'perkedel-kentang',
                'description' => 'Olahan kentang pilihan yang ditumbuk lembut berpadu bumbu rempah bawang putih dan seledri harum.',
                'price' => 4000,
                'image_url' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=600&q=80',
                'stock' => 35,
                'min_stock' => 8,
            ],
            [
                'category_id' => $lauk->id,
                'name' => 'Orek Tempe',
                'slug' => 'orek-tempe',
                'description' => 'Tempe kedelai dipotong korek api digoreng renyah, dibalut bumbu karamel kecap manis dan irisan cabai merah.',
                'price' => 5000,
                'image_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80',
                'stock' => 30,
                'min_stock' => 5,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Es Jeruk Peras',
                'slug' => 'es-jeruk-peras',
                'description' => 'Perasan jeruk segar asli dengan gula tebu asli dan es batu kristal, manis asam segar pelepas dahaga.',
                'price' => 7000,
                'image_url' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80',
                'stock' => 50,
                'min_stock' => 10,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Es Teh Manis',
                'slug' => 'es-teh-manis',
                'description' => 'Seduhan teh melati tradisional khas Nusantara disajikan dingin manis menyegarkan.',
                'price' => 5000,
                'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80',
                'stock' => 60,
                'min_stock' => 15,
            ],
        ];

        foreach ($menus as $menuData) {
            $stockQty = $menuData['stock'];
            $minStock = $menuData['min_stock'];
            unset($menuData['stock'], $menuData['min_stock']);

            $menu = Menu::query()->firstOrCreate(
                ['slug' => $menuData['slug']],
                $menuData + ['is_available' => true]
            );

            DailyStock::query()->firstOrCreate(
                [
                    'menu_id' => $menu->id,
                    'date' => today()->toDateString(),
                ],
                ['quantity' => $stockQty, 'minimum_stock' => $minStock]
            );
        }
    }
}
