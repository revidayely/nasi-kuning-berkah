<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmTransactionPayment;
use App\Models\Category;
use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $today = today()->toDateString();

        $orders = Transaction::query()
            ->with(['details.menu', 'cashier'])
            ->where(function ($query) {
                $query->where('status', 'paid')
                    ->orWhere(fn ($pending) => $pending
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now()));
            })
            ->where('kitchen_status', '!=', 'completed')
            ->latest()
            ->get();

        $completedToday = Transaction::query()
            ->whereDate('updated_at', $today)
            ->where('kitchen_status', 'completed')
            ->count();

        $menus = Menu::query()
            ->with(['category', 'dailyStocks' => fn ($q) => $q->where('date', $today)])
            ->where('is_available', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->whereHas('menus')
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('orders', 'completedToday', 'menus', 'categories'));
    }

    public function updateKitchenStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'kitchen_status' => ['required', 'in:new,preparing,ready,completed'],
        ]);

        $transaction->update($validated);

        return redirect()->route('pos.index')->with('success', "Status pesanan #{$transaction->id} diperbarui.");
    }

    public function confirmPayment(Transaction $transaction): RedirectResponse
    {
        try {
            $confirmed = app(ConfirmTransactionPayment::class)->handle($transaction->id);
        } catch (\RuntimeException $exception) {
            return redirect()->route('pos.index')->with('error', $exception->getMessage());
        }

        if ($confirmed->status === 'cancelled') {
            return redirect()->route('pos.index')->with('error', 'Pesanan kedaluwarsa dan dibatalkan. Pelanggan perlu membuat pesanan baru.');
        }

        return redirect()->route('pos.index')->with('success', "Pembayaran pesanan #{$transaction->id} dikonfirmasi; stok telah diperbarui.");
    }
}
