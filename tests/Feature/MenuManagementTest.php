<?php

namespace Tests\Feature;

use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_menu_and_initial_stock_from_management_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('menus.manage'))
            ->assertOk()
            ->assertSee('Tambah item menu');

        $this->actingAs($owner)
            ->post(route('menus.store'), [
                'category_name' => 'Paket Spesial',
                'name' => 'Nasi Kuning Komplit',
                'description' => 'Paket lengkap dengan lauk.',
                'price' => 25000,
                'initial_stock' => 18,
                'minimum_stock' => 4,
                'is_available' => '1',
            ])
            ->assertRedirect(route('menus.manage'))
            ->assertSessionHas('success');

        $menu = Menu::query()->where('name', 'Nasi Kuning Komplit')->firstOrFail();

        $this->assertSame('paket-spesial', $menu->category->slug);
        $this->assertTrue($menu->is_available);
        $this->assertDatabaseHas('daily_stocks', [
            'menu_id' => $menu->id,
            'date' => today()->toDateString(),
            'quantity' => 18,
            'minimum_stock' => 4,
        ]);
    }

    public function test_cashier_can_add_menu_and_initial_stock(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('staff.menus'))
            ->assertOk()
            ->assertSee('Kelola menu');

        $this->actingAs($cashier)->post(route('menus.store'), [
            'category_name' => 'Lauk',
            'name' => 'Ayam Bakar',
            'price' => 22000,
            'initial_stock' => 10,
            'minimum_stock' => 2,
        ])->assertRedirect(route('menus.manage'));

        $this->assertDatabaseHas('menus', ['name' => 'Ayam Bakar']);
        $this->assertDatabaseHas('daily_stocks', ['quantity' => 10, 'minimum_stock' => 2]);
    }

    public function test_menu_can_use_existing_category(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = \App\Models\Category::query()->create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        $this->actingAs($owner)->post(route('menus.store'), [
            'category_id' => $category->id,
            'name' => 'Es Teh',
            'price' => 5000,
            'initial_stock' => 25,
            'minimum_stock' => 5,
        ])->assertRedirect(route('menus.manage'));

        $this->assertDatabaseHas('menus', [
            'category_id' => $category->id,
            'name' => 'Es Teh',
        ]);
        $this->assertSame(25, DailyStock::query()->firstOrFail()->quantity);
    }

    public function test_owner_and_cashier_can_update_menu_description_price_and_image(): void
    {
        Storage::fake('public');
        $category = \App\Models\Category::query()->create([
            'name' => 'Paket Nasi',
            'slug' => 'paket-nasi',
        ]);
        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Kuning',
            'slug' => 'nasi-kuning',
            'description' => 'Deskripsi lama',
            'price' => 15000,
            'image_url' => 'https://example.com/lama.jpg',
            'is_available' => true,
        ]);

        foreach (['owner', 'cashier'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->patch(route('menus.update', $menu), [
                    'description' => "Deskripsi dari {$role}",
                    'price' => 23000,
                    'image' => UploadedFile::fake()->image("{$role}.jpg"),
                ])
                ->assertRedirect(route('menus.manage'))
                ->assertSessionHas('success');

            $menu->refresh();
            $this->assertSame("Deskripsi dari {$role}", $menu->description);
            $this->assertSame(23000, $menu->price);
            $this->assertStringContainsString('/storage/menus/', $menu->image_url);
        }

        $this->assertCount(2, Storage::disk('public')->allFiles('menus'));
    }
}
