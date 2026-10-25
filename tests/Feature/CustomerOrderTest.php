<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.warung.qris_image', 'https://example.test/qris.png');
    }

    public function test_customer_can_create_qris_order_and_is_shown_confirmation_page_without_deducting_stock(): void
    {
        [$menu, $stock] = $this->createAvailableMenu(5);

        $response = $this->post(route('orders.store'), [
            'customer_name' => 'Sari',
            'customer_phone' => '081234567890',
            'payment_method' => 'qris',
            'items' => json_encode([
                ['menu_id' => $menu->id, 'quantity' => 2],
            ]),
        ]);

        $transaction = Transaction::query()->firstOrFail();

        $response->assertRedirect(route('orders.confirmation'));
        $this->assertSame(36000, $transaction->total_amount);
        $this->assertSame('pending', $transaction->status);
        $this->assertSame('qris', $transaction->payment_method);
        $this->assertSame('pending', $transaction->payment_status);
        $this->assertSame(5, $stock->fresh()->quantity);

        $this->withSession(['customer_order_id' => $transaction->id])
            ->get(route('orders.confirmation'))
            ->assertOk()
            ->assertSee('Bayar menggunakan QRIS')
            ->assertSee('Screenshot bukti QRIS')
            ->assertSee('manual sebelum stok dikurangi');
    }

    public function test_customer_can_create_pay_at_warung_order(): void
    {
        [$menu] = $this->createAvailableMenu(5);

        $response = $this->post(route('orders.store'), [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'payment_method' => 'pickup',
            'items' => json_encode([
                ['menu_id' => $menu->id, 'quantity' => 1],
            ]),
        ]);

        $transaction = Transaction::query()->firstOrFail();

        $response->assertRedirect(route('orders.confirmation'));
        $this->withSession(['customer_order_id' => $transaction->id])
            ->get(route('orders.confirmation'))
            ->assertOk()
            ->assertSee('Bayar langsung di warung');
    }

    public function test_customer_cannot_order_more_than_available_stock(): void
    {
        [$menu] = $this->createAvailableMenu(1);

        $response = $this->from(route('home'))->post(route('orders.store'), [
            'customer_name' => 'Sari',
            'customer_phone' => '081234567890',
            'payment_method' => 'qris',
            'items' => json_encode([
                ['menu_id' => $menu->id, 'quantity' => 2],
            ]),
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_customer_order_appears_in_cashier_queue_and_payment_confirmation_deducts_stock_once(): void
    {
        Storage::fake('local');
        [$menu, $stock] = $this->createAvailableMenu(5);

        $this->post(route('orders.store'), [
            'customer_name' => 'Maya',
            'customer_phone' => '081234567890',
            'payment_method' => 'qris',
            'items' => json_encode([
                ['menu_id' => $menu->id, 'quantity' => 2],
            ]),
        ]);

        $transaction = Transaction::query()->firstOrFail();
        $proofUrl = URL::temporarySignedRoute('orders.proof', now()->addHour(), ['transaction' => $transaction->id]);
        $this->post($proofUrl, ['payment_proof' => UploadedFile::fake()->image('proof.png')])
            ->assertRedirect(route('orders.confirmation'));

        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Antrean Pesanan')
            ->assertSee('Maya')
            ->assertSee('Lihat bukti QRIS')
            ->assertSee('Konfirmasi pembayaran');

        $this->actingAs($cashier)
            ->post(route('pos.orders.payment', $transaction))
            ->assertRedirect(route('pos.index'));

        $this->assertSame(3, $stock->fresh()->quantity);
        $this->assertSame('paid', $transaction->fresh()->status);

        $this->actingAs($cashier)->post(route('pos.orders.payment', $transaction));
        $this->assertSame(3, $stock->fresh()->quantity);
    }

    public function test_cashier_can_create_initial_daily_stock_from_stock_page(): void
    {
        [$menu] = $this->createAvailableMenu(0);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('stock.index'))
            ->assertOk()
            ->assertSee('Nasi Kuning Ayam');

        $this->actingAs($cashier)
            ->post(route('stock.store'), [
                'stocks' => [
                    [
                        'menu_id' => $menu->id,
                        'quantity' => 12,
                        'minimum_stock' => 3,
                    ],
                ],
            ])
            ->assertRedirect(route('stock.index'));

        $this->assertDatabaseHas('daily_stocks', [
            'menu_id' => $menu->id,
            'date' => today()->toDateString(),
            'quantity' => 12,
            'minimum_stock' => 3,
        ]);
    }

    /** @return array{Menu, DailyStock} */
    private function createAvailableMenu(int $quantity): array
    {
        $category = Category::query()->create([
            'name' => 'Paket Nasi',
            'slug' => 'paket-nasi',
        ]);

        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Kuning Ayam',
            'slug' => 'nasi-kuning-ayam',
            'description' => 'Nasi kuning dengan ayam.',
            'price' => 18000,
            'is_available' => true,
        ]);

        $stock = DailyStock::query()->create([
            'menu_id' => $menu->id,
            'date' => today()->toDateString(),
            'quantity' => $quantity,
            'minimum_stock' => 1,
        ]);

        return [$menu, $stock];
    }
}
