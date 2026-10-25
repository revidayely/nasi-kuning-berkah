<?php

namespace App\Http\Controllers;

use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function customerStore(Request $request): RedirectResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge([
                'items' => json_decode($request->input('items'), true) ?? [],
            ]);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:qris,pickup'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'distinct', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        if ($data['payment_method'] === 'qris' && ! config('services.warung.qris_image')) {
            throw ValidationException::withMessages([
                'payment_method' => 'QRIS belum disiapkan warung. Silakan pilih pembayaran di warung.',
            ]);
        }

        $transaction = DB::transaction(function () use ($data) {
            $total = 0;
            $items = [];

            foreach ($data['items'] as $item) {
                $menu = Menu::query()->whereKey($item['menu_id'])->where('is_available', true)->firstOrFail();
                $stock = DailyStock::query()
                    ->where('menu_id', $menu->id)
                    ->where('date', today()->toDateString())
                    ->lockForUpdate()
                    ->first();

                if (! $stock || $stock->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak mencukupi.",
                    ]);
                }

                $reservedQuantity = TransactionDetail::query()
                    ->where('menu_id', $menu->id)
                    ->whereHas('transaction', fn ($query) => $query
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now()))
                    ->sum('quantity');

                if (($stock->quantity - $reservedQuantity) < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} sedang dipesan pelanggan lain.",
                    ]);
                }

                $subtotal = $menu->price * $item['quantity'];
                $total += $subtotal;
                $items[] = [$menu, $item['quantity'], $subtotal];
            }

            $transaction = Transaction::query()->create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'expires_at' => now()->addMinutes(30),
                'total_amount' => $total,
                'received_amount' => 0,
                'change_amount' => 0,
            ]);

            foreach ($items as [$menu, $quantity, $subtotal]) {
                TransactionDetail::query()->create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);
            }

            return $transaction->load('details.menu');
        });

        $request->session()->put('customer_order_id', $transaction->id);

        return redirect()->route('orders.confirmation');
    }

    public function customerConfirmation(Request $request): View|RedirectResponse
    {
        $transactionId = $request->session()->get('customer_order_id');
        $transaction = Transaction::query()->with('details.menu')->find($transactionId);

        if (! $transaction) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan. Silakan buat pesanan baru.');
        }

        $itemsText = $transaction->details
            ->map(fn (TransactionDetail $detail) => "- {$detail->menu->name} x{$detail->quantity}")
            ->implode("\n");
        $paymentText = $transaction->payment_method === 'qris'
            ? ($transaction->receipt_image
                ? 'Saya sudah membayar via QRIS dan mengunggah bukti pembayaran untuk pesanan ini.'
                : 'Saya akan membayar via QRIS dan mengirimkan bukti pembayaran untuk pesanan ini.')
            : 'Saya memilih bayar langsung saat mengambil pesanan di warung.';
        $message = "Halo Warung Nasi Kuning, konfirmasi pesanan #{$transaction->id}.\nNama: {$transaction->customer_name}\nTelepon: {$transaction->customer_phone}\n{$itemsText}\nTotal: Rp ".number_format($transaction->total_amount, 0, ',', '.')."\n{$paymentText}";
        $phone = preg_replace('/\\D+/', '', (string) config('services.warung.whatsapp', ''));
        $whatsappUrl = $phone !== '' ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message) : null;
        $proofUrl = $transaction->payment_method === 'qris' && ! $transaction->receipt_image
            ? URL::temporarySignedRoute('orders.proof', now()->addHours(2), ['transaction' => $transaction])
            : null;

        return view('landing.order-confirmation', compact('transaction', 'whatsappUrl', 'proofUrl'));
    }

    public function uploadPaymentProof(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_unless(
            $transaction->payment_method === 'qris'
                && $transaction->status === 'pending'
                && $transaction->expires_at?->isFuture(),
            404
        );

        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $validated['payment_proof']->store('payment-proofs', 'local');

        DB::transaction(function () use ($transaction, $path): void {
            $locked = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending' || $locked->payment_method !== 'qris' || ! $locked->expires_at?->isFuture()) {
                Storage::disk('local')->delete($path);
                abort(404);
            }

            $previousProof = $locked->receipt_image;
            $locked->update([
                'receipt_image' => $path,
                'payment_status' => 'proof_submitted',
            ]);

            if ($previousProof) {
                Storage::disk('local')->delete($previousProof);
            }
        });

        return redirect()->route('orders.confirmation')->with('success', 'Bukti QRIS berhasil diunggah. Tunggu verifikasi warung.');
    }

    public function showPaymentProof(Transaction $transaction): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($transaction->receipt_image, 404);

        return Storage::disk('local')->response($transaction->receipt_image);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'distinct', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'received_amount' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $transaction = DB::transaction(function () use ($data) {
            $total = 0;
            $items = [];

            foreach ($data['items'] as $item) {
                $menu = Menu::query()->whereKey($item['menu_id'])->where('is_available', true)->firstOrFail();
                $stock = DailyStock::query()
                    ->where('menu_id', $menu->id)
                    ->where('date', today()->toDateString())
                    ->lockForUpdate()
                    ->first();

                $reserved = TransactionDetail::query()
                    ->where('menu_id', $menu->id)
                    ->whereHas('transaction', fn ($query) => $query
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now()))
                    ->sum('quantity');

                if (! $stock || $stock->quantity - $reserved < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak mencukupi.",
                    ]);
                }

                $subtotal = $menu->price * $item['quantity'];
                $total += $subtotal;
                $items[] = [$menu, $item['quantity'], $subtotal];
            }

            if ($data['received_amount'] < $total) {
                throw ValidationException::withMessages([
                    'received_amount' => 'Nominal uang diterima kurang dari total transaksi.',
                ]);
            }

            $transaction = Transaction::query()->create([
                'customer_name' => $data['customer_name'] ?? 'Umum',
                'customer_phone' => $data['customer_phone'] ?? null,
                'cashier_id' => auth()->id(),
                'status' => 'pending',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'kitchen_status' => 'new',
                'expires_at' => now()->addMinutes(30),
                'total_amount' => $total,
                'received_amount' => $data['received_amount'] ?? $total,
                'change_amount' => ($data['received_amount'] ?? $total) - $total,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as [$menu, $quantity, $subtotal]) {
                TransactionDetail::query()->create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);
            }

            return $transaction->load('details.menu');
        });

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'transaction' => $transaction,
        ], 201);
    }

    public function posStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'order_type' => ['nullable', 'string', 'in:dine_in,takeaway'],
            'payment_method' => ['required', 'string', 'in:cash,qris'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'distinct', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'received_amount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = DB::transaction(function () use ($data) {
            $total = 0;
            $items = [];

            $menuIds = collect($data['items'])->pluck('menu_id')->sort()->values()->all();
            $stocks = DailyStock::query()
                ->whereIn('menu_id', $menuIds)
                ->where('date', today()->toDateString())
                ->orderBy('menu_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('menu_id');

            foreach ($data['items'] as $item) {
                $menu = Menu::query()->whereKey($item['menu_id'])->where('is_available', true)->firstOrFail();
                $stock = $stocks->get($item['menu_id']);

                if (! $stock || $stock->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak mencukupi (sisa: " . ($stock?->quantity ?? 0) . ").",
                    ]);
                }

                $subtotal = $menu->price * $item['quantity'];
                $total += $subtotal;
                $items[] = [$menu, $stock, $item['quantity'], $subtotal];
            }

            $receivedAmount = $data['payment_method'] === 'cash'
                ? (int) ($data['received_amount'] ?? $total)
                : $total;

            if ($data['payment_method'] === 'cash' && $receivedAmount < $total) {
                throw ValidationException::withMessages([
                    'received_amount' => 'Nominal uang diterima kurang dari total belanja.',
                ]);
            }

            $changeAmount = max(0, $receivedAmount - $total);

            // Potong stok lauk harian secara atomik
            foreach ($items as [$menu, $stock, $quantity, $subtotal]) {
                $stock->quantity -= $quantity;
                $stock->save();
            }

            $customerLabel = $data['customer_name'] ?: 'Pelanggan Kasir';
            if (! empty($data['order_type'])) {
                $typeLabel = $data['order_type'] === 'dine_in' ? '[Makan di Tempat]' : '[Bawa Pulang]';
                $customerLabel = "{$typeLabel} {$customerLabel}";
            }

            $transaction = Transaction::query()->create([
                'customer_name' => $customerLabel,
                'customer_phone' => $data['customer_phone'] ?? null,
                'cashier_id' => auth()->id(),
                'status' => 'paid',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'kitchen_status' => 'preparing',
                'paid_at' => now(),
                'total_amount' => $total,
                'received_amount' => $receivedAmount,
                'change_amount' => $changeAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as [$menu, $stock, $quantity, $subtotal]) {
                TransactionDetail::query()->create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);
            }

            return $transaction->load(['details.menu', 'cashier']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi POS berhasil diselesaikan dan stok telah dipotong.',
            'transaction' => $transaction,
            'receipt_url' => route('pos.orders.receipt', $transaction),
        ], 201);
    }

    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['details.menu', 'cashier']);

        return view('pos.receipt', compact('transaction'));
    }
}
