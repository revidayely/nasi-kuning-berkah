<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_pos_and_stock_but_cannot_open_owner_dashboard(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();
        $this->actingAs($cashier)->get(route('stock.index'))->assertOk();
        $this->actingAs($cashier)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($cashier)->get('/api/dashboard-summary')->assertForbidden();
    }

    public function test_owner_can_open_dashboard_pos_and_stock(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('pos.index'))->assertOk();
        $this->actingAs($owner)->get(route('stock.index'))->assertOk();
        $this->actingAs($owner)->get('/api/dashboard-summary')->assertOk();
    }

    public function test_unauthenticated_users_cannot_open_internal_pages(): void
    {
        $this->get(route('pos.index'))->assertRedirect(route('login'));
        $this->get(route('stock.index'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_redirects_owner_to_dashboard_and_cashier_to_order_queue(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.test',
            'password' => 'password',
            'role' => 'owner',
        ]);

        $this->post(route('login'), [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        auth()->logout();

        $cashier = User::factory()->create([
            'email' => 'cashier@example.test',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->post(route('login'), [
            'email' => $cashier->email,
            'password' => 'password',
        ])->assertRedirect('/pos');
    }

    public function test_owner_dashboard_includes_cashier_created_orders_from_previous_days(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $cashier = User::factory()->create(['role' => 'cashier', 'name' => 'Kasir Pagi']);
        $category = Category::query()->create(['name' => 'Paket', 'slug' => 'paket']);
        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Uduk',
            'slug' => 'nasi-uduk',
            'price' => 15000,
            'is_available' => true,
        ]);

        $transaction = Transaction::query()->create([
            'customer_name' => 'Pelanggan Kasir',
            'cashier_id' => $cashier->id,
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'kitchen_status' => 'completed',
            'total_amount' => 15000,
            'received_amount' => 15000,
            'change_amount' => 0,
            'paid_at' => Carbon::yesterday(),
            'created_at' => Carbon::yesterday(),
            'updated_at' => Carbon::yesterday(),
        ]);

        TransactionDetail::query()->create([
            'transaction_id' => $transaction->id,
            'menu_id' => $menu->id,
            'quantity' => 1,
            'unit_price' => 15000,
            'subtotal' => 15000,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pelanggan Kasir')
            ->assertSee('Kasir Pagi')
            ->assertSee('Nasi Uduk');
    }
}
