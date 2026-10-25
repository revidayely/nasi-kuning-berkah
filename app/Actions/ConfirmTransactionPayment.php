<?php

namespace App\Actions;

use App\Models\DailyStock;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmTransactionPayment
{
    public function handle(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId): Transaction {
            $transaction = Transaction::query()
                ->whereKey($transactionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'paid') {
                return $transaction->load('details.menu');
            }

            if ($transaction->status !== 'pending') {
                throw new RuntimeException('Pesanan yang dibatalkan tidak dapat dibayar.');
            }

            if (! $transaction->expires_at || $transaction->expires_at->isPast()) {
                $transaction->update(['status' => 'cancelled']);

                return $transaction;
            }

            if ($transaction->payment_method === 'qris' && ! $transaction->receipt_image) {
                throw new RuntimeException('Bukti pembayaran QRIS belum diunggah.');
            }

            $details = $transaction->details()->with('menu')->orderBy('menu_id')->get();

            if ($details->isEmpty()) {
                throw new RuntimeException('Pesanan tidak memiliki item.');
            }

            $stocks = DailyStock::query()
                ->whereIn('menu_id', $details->pluck('menu_id')->unique())
                ->where('date', today()->toDateString())
                ->orderBy('menu_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('menu_id');

            foreach ($details as $detail) {
                $stock = $stocks->get($detail->menu_id);
                if (! $stock || $stock->quantity < $detail->quantity) {
                    throw new RuntimeException("Stok {$detail->menu->name} tidak mencukupi.");
                }
            }

            foreach ($details as $detail) {
                $stock = $stocks->get($detail->menu_id);
                $stock->quantity -= $detail->quantity;
                $stock->save();
            }

            $transaction->forceFill([
                'status' => 'paid',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'received_amount' => $transaction->payment_method === 'cash'
                    ? $transaction->received_amount
                    : $transaction->total_amount,
                'change_amount' => $transaction->payment_method === 'cash'
                    ? max(0, $transaction->received_amount - $transaction->total_amount)
                    : 0,
            ])->save();

            return $transaction->load('details.menu');
        });
    }
}
