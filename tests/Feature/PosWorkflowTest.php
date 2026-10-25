<?php

namespace Tests\Feature;

use App\Actions\ConfirmTransactionPayment;
use App\Models\Category;
use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_transaction_auto_deducts_daily_stock(): void
    {
        $category = Category::create([
            'name' => 'Nasi Kuning',
            'slug' => 'nasi-kuning',
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Nasi Kuning Ayam',
            'slug' => 'nasi-kuning-ayam',
            'description' => 'Cocok untuk sarapan.',
            'price' => 20000,
            'is_available' => true,
        ]);

        DailyStock::create([
            'menu_id' => $menu->id,
            'date' => now()->toDateString(),
            'quantity' => 10,
        ]);

        $transaction = Transaction::create([
            'customer_name' => 'Tamu',
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'kitchen_status' => 'new',
            'total_amount' => 40000,
            'received_amount' => 50000,
            'change_amount' => 10000,
            'expires_at' => now()->addMinutes(30),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
        ]);

        app(ConfirmTransactionPayment::class)->handle($transaction->id);

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertNotNull($transaction->fresh()->paid_at);

        $this->assertDatabaseHas('daily_stocks', [
            'menu_id' => $menu->id,
            'date' => now()->toDateString(),
            'quantity' => 8,
        ]);
    }

    public function test_cashier_can_execute_direct_pos_transaction_with_atomic_stock_deduction(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Paket', 'slug' => 'paket']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Paket Komplit',
            'slug' => 'paket-komplit',
            'price' => 25000,
            'is_available' => true,
        ]);

        $stock = DailyStock::create([
            'menu_id' => $menu->id,
            'date' => today()->toDateString(),
            'quantity' => 15,
            'minimum_stock' => 3,
        ]);

        $response = $this->actingAs($cashier)->postJson(route('pos.transactions.store'), [
            'customer_name' => 'Budi Meja 4',
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'received_amount' => 50000,
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'transaction' => [
                'status' => 'paid',
                'payment_status' => 'paid',
                'total_amount' => 50000,
                'received_amount' => 50000,
                'change_amount' => 0,
            ],
        ]);

        $this->assertSame(13, $stock->fresh()->quantity);

        $transactionId = $response->json('transaction.id');
        $this->actingAs($cashier)
            ->get(route('pos.orders.receipt', $transactionId))
            ->assertOk()
            ->assertSee('WARUNG NASI KUNING BERKAH')
            ->assertSee('Paket Komplit')
            ->assertSee('50.000');
    }

    public function test_cashier_cannot_order_more_than_pos_stock(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Lauk', 'slug' => 'lauk']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Ayam Goreng',
            'slug' => 'ayam-goreng',
            'price' => 12000,
            'is_available' => true,
        ]);

        DailyStock::create([
            'menu_id' => $menu->id,
            'date' => today()->toDateString(),
            'quantity' => 2,
            'minimum_stock' => 1,
        ]);

        $response = $this->actingAs($cashier)->postJson(route('pos.transactions.store'), [
            'customer_name' => 'Tamu',
            'payment_method' => 'cash',
            'received_amount' => 50000,
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 5],
            ],
        ]);

        $response->assertUnprocessable();
    }
}
