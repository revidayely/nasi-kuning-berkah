<?php

namespace Tests\Feature;

use App\Actions\ConfirmTransactionPayment;
use App\Models\Category;
use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.warung.qris_image', 'https://example.test/qris.png');
        Config::set('services.warung.whatsapp', '628123456789');
    }

    public function test_cashier_api_ignores_forged_paid_status_and_rejects_overselling(): void
    {
        [$menu, $stock] = $this->createMenuWithStock(2);
        $cashier = User::factory()->create(['role' => 'cashier']);

        $response = $this->actingAs($cashier)->postJson('/api/transactions', [
            'customer_name' => 'Pelanggan Kasir',
            'status' => 'paid',
            'received_amount' => 20000,
            'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('transactions', [
            'id' => $response->json('transaction.id'),
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        $this->assertSame(2, $stock->fresh()->quantity);

        $this->actingAs($cashier)->postJson('/api/transactions', [
            'customer_name' => 'Oversell',
            'status' => 'paid',
            'received_amount' => 90000,
            'items' => [['menu_id' => $menu->id, 'quantity' => 3]],
        ])->assertUnprocessable();
    }

    public function test_qris_payment_requires_uploaded_proof_and_staff_can_view_private_file(): void
    {
        Storage::fake('local');
        [$menu, $stock] = $this->createMenuWithStock(4);

        $this->post(route('orders.store'), [
            'customer_name' => 'Nadia',
            'customer_phone' => '081234567890',
            'payment_method' => 'qris',
            'items' => json_encode([['menu_id' => $menu->id, 'quantity' => 2]]),
        ])->assertRedirect(route('orders.confirmation'));

        $transaction = Transaction::query()->firstOrFail();
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->post(route('pos.orders.payment', $transaction))
            ->assertRedirect(route('pos.index'))
            ->assertSessionHas('error');
        $this->assertSame(4, $stock->fresh()->quantity);

        $signedUrl = URL::temporarySignedRoute('orders.proof', now()->addHour(), [
            'transaction' => $transaction->id,
        ]);
        $this->post($signedUrl, [
            'payment_proof' => UploadedFile::fake()->image('qris-proof.jpg'),
        ])->assertRedirect(route('orders.confirmation'));

        $transaction->refresh();
        $this->assertSame('proof_submitted', $transaction->payment_status);
        $this->assertNotNull($transaction->receipt_image);
        Storage::disk('local')->assertExists($transaction->receipt_image);

        $this->actingAs($cashier)
            ->get(route('staff.orders.proof', $transaction))
            ->assertOk();

        $this->actingAs($cashier)
            ->post(route('pos.orders.payment', $transaction))
            ->assertRedirect(route('pos.index'));

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame(2, $stock->fresh()->quantity);

        app(ConfirmTransactionPayment::class)->handle($transaction->id);
        $this->assertSame(2, $stock->fresh()->quantity);
    }

    public function test_qris_checkout_is_rejected_when_real_qr_is_not_configured(): void
    {
        Config::set('services.warung.qris_image', null);
        [$menu] = $this->createMenuWithStock(3);

        $this->from(route('home'))
            ->post(route('orders.store'), [
                'customer_name' => 'Rani',
                'customer_phone' => '081234567890',
                'payment_method' => 'qris',
                'items' => json_encode([['menu_id' => $menu->id, 'quantity' => 1]]),
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('transactions', 0);
    }

    /** @return array{Menu, DailyStock} */
    private function createMenuWithStock(int $quantity): array
    {
        $category = Category::query()->create(['name' => 'Paket', 'slug' => 'paket']);
        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Kuning Ayam',
            'slug' => 'nasi-kuning-ayam',
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
