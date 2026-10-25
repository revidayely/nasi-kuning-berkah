<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_create_staff(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('staff.manage'))
            ->assertOk()
            ->assertSee('Kelola Akun Staf');

        $response = $this->actingAs($owner)->post(route('staff.store'), [
            'name' => 'Siti Kasir Baru',
            'email' => 'siti@warung.test',
            'role' => 'cashier',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('staff.manage'));
        $this->assertDatabaseHas('users', [
            'email' => 'siti@warung.test',
            'role' => 'cashier',
        ]);
    }

    public function test_cashier_cannot_access_staff_management(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('staff.manage'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->post(route('staff.store'), [
                'name' => 'Hacker',
                'email' => 'hack@warung.test',
                'role' => 'owner',
                'password' => 'password123',
            ])
            ->assertForbidden();
    }

    public function test_owner_can_export_csv_and_print_dashboard(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::create(['name' => 'Paket', 'slug' => 'paket']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Paket Komplit',
            'slug' => 'paket-komplit',
            'price' => 25000,
            'is_available' => true,
        ]);

        $trx = Transaction::create([
            'customer_name' => 'Pelanggan Setia',
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'total_amount' => 25000,
            'received_amount' => 25000,
            'change_amount' => 0,
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'menu_id' => $menu->id,
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard.export', ['range' => 'today']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('Pelanggan Setia')
            ->assertSee('25000');

        $this->actingAs($owner)
            ->get(route('dashboard.print', ['range' => 'today']))
            ->assertOk()
            ->assertSee('Laporan Rekapitulasi Penjualan')
            ->assertSee('Pelanggan Setia')
            ->assertSee('25.000');
    }
}

