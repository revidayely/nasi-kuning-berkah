<?php

namespace App\Http\Controllers;

use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = today()->toDateString();
        $range = $request->input('range', 'today');

        [$startDate, $endDate, $rangeLabel] = match ($range) {
            'yesterday' => [
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
                'Kemarin ('.Carbon::yesterday()->format('d M Y').')',
            ],
            '7days' => [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
                '7 Hari Terakhir',
            ],
            'this_month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                'Bulan Ini ('.now()->translatedFormat('F Y').')',
            ],
            'custom' => [
                Carbon::parse($request->input('start_date', $today))->startOfDay(),
                Carbon::parse($request->input('end_date', $today))->endOfDay(),
                Carbon::parse($request->input('start_date', $today))->format('d M').' - '.Carbon::parse($request->input('end_date', $today))->format('d M Y'),
            ],
            default => [
                now()->startOfDay(),
                now()->endOfDay(),
                'Hari Ini ('.now()->format('d M Y').')',
            ],
        };

        // Omset berdasarkan range terpilih
        $revenue = (int) Transaction::query()
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total transaksi lunas dalam range
        $transactionCount = Transaction::query()
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->count();

        // Rata-rata nilai pesanan (AOV)
        $averageOrderValue = $transactionCount > 0 ? (int) round($revenue / $transactionCount) : 0;

        // Omset harian & bulanan untuk perbandingan
        $dailyRevenue = (int) Transaction::query()
            ->whereDate('paid_at', $today)
            ->where('status', 'paid')
            ->sum('total_amount');

        $monthlyRevenue = (int) Transaction::query()
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', 'paid')
            ->sum('total_amount');

        $transactionsToday = Transaction::query()
            ->whereDate('created_at', $today)
            ->count();

        $pendingOrders = Transaction::query()
            ->where('status', 'pending')
            ->whereDate('created_at', $today)
            ->count();

        $qrisWaiting = Transaction::query()
            ->where('payment_method', 'qris')
            ->whereIn('payment_status', ['pending', 'proof_submitted'])
            ->whereDate('created_at', $today)
            ->count();

        // Top 5 Menu terlaris dalam rentang waktu yang dipilih
        $topMenus = Menu::query()
            ->select('menus.*', DB::raw('SUM(transaction_details.quantity) as sold_quantity'), DB::raw('SUM(transaction_details.subtotal) as menu_revenue'))
            ->join('transaction_details', 'transaction_details.menu_id', '=', 'menus.id')
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->whereBetween('transactions.paid_at', [$startDate, $endDate])
            ->where('transactions.status', 'paid')
            ->groupBy('menus.id', 'menus.category_id', 'menus.name', 'menus.slug', 'menus.description', 'menus.price', 'menus.image_url', 'menus.is_available', 'menus.created_at', 'menus.updated_at')
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get();

        // Peringatan stok
        $stockAlerts = DailyStock::query()
            ->with('menu')
            ->where('date', $today)
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->get();

        // Pesanan terbaru
        $recentOrders = Transaction::query()
            ->with(['details.menu', 'cashier'])
            ->latest()
            ->limit(20)
            ->get();

        // Tren penjualan 7 hari terakhir (untuk chart visual)
        $dailyTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $label = now()->subDays($daysAgo)->format('d M');
            $omset = (int) Transaction::query()
                ->whereDate('paid_at', $date)
                ->where('status', 'paid')
                ->sum('total_amount');
            $count = Transaction::query()
                ->whereDate('paid_at', $date)
                ->where('status', 'paid')
                ->count();

            return [
                'date' => $date,
                'label' => $label,
                'omset' => $omset,
                'count' => $count,
            ];
        });

        $maxTrendOmset = max(1, $dailyTrend->max('omset'));

        return view('dashboard.index', compact(
            'revenue',
            'transactionCount',
            'averageOrderValue',
            'dailyRevenue',
            'monthlyRevenue',
            'transactionsToday',
            'pendingOrders',
            'qrisWaiting',
            'topMenus',
            'stockAlerts',
            'recentOrders',
            'dailyTrend',
            'maxTrendOmset',
            'range',
            'rangeLabel',
            'startDate',
            'endDate'
        ));
    }

    public function summary(): JsonResponse
    {
        $today = today()->toDateString();

        return response()->json([
            'daily_revenue' => Transaction::query()
                ->whereDate('paid_at', $today)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'transactions_today' => Transaction::query()->whereDate('created_at', $today)->count(),
            'stock_alerts' => DailyStock::query()->where('date', $today)->whereColumn('quantity', '<=', 'minimum_stock')->count(),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $range = $request->input('range', 'this_month');
        $today = today()->toDateString();

        [$startDate, $endDate] = match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [
                Carbon::parse($request->input('start_date', $today))->startOfDay(),
                Carbon::parse($request->input('end_date', $today))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $transactions = Transaction::query()
            ->with(['details.menu', 'cashier'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->orderBy('paid_at')
            ->get();

        $filename = 'laporan-penjualan-'.now()->format('Ymd-His').'.csv';
        $handle = fopen('php://temp', 'r+');

        // Header CSV
        fputcsv($handle, ['No. Transaksi', 'Tanggal Bayar', 'Nama Pelanggan', 'Metode Pembayaran', 'Kasir', 'Detail Item', 'Total Transaksi (Rp)']);

        foreach ($transactions as $t) {
            $itemsSummary = $t->details->map(fn ($d) => "{$d->menu?->name} ({$d->quantity}x)")->join(', ');
            fputcsv($handle, [
                '#'.$t->id,
                $t->paid_at?->format('Y-m-d H:i:s') ?? '',
                $t->customer_name,
                $t->payment_method === 'qris' ? 'QRIS' : 'Tunai',
                $t->cashier?->name ?? 'Online Website',
                $itemsSummary,
                $t->total_amount,
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function printReport(Request $request): View
    {
        $range = $request->input('range', 'today');
        $today = today()->toDateString();

        [$startDate, $endDate, $rangeLabel] = match ($range) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay(), 'Kemarin ('.Carbon::yesterday()->format('d M Y').')'],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay(), '7 Hari Terakhir'],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth(), 'Bulan Ini ('.now()->translatedFormat('F Y').')'],
            'custom' => [
                Carbon::parse($request->input('start_date', $today))->startOfDay(),
                Carbon::parse($request->input('end_date', $today))->endOfDay(),
                Carbon::parse($request->input('start_date', $today))->format('d M Y').' s/d '.Carbon::parse($request->input('end_date', $today))->format('d M Y'),
            ],
            default => [now()->startOfDay(), now()->endOfDay(), 'Hari Ini ('.now()->format('d M Y').')'],
        };

        $transactions = Transaction::query()
            ->with(['details.menu', 'cashier'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->orderBy('paid_at')
            ->get();

        $totalRevenue = $transactions->sum('total_amount');
        $cashRevenue = $transactions->where('payment_method', 'cash')->sum('total_amount');
        $qrisRevenue = $transactions->where('payment_method', 'qris')->sum('total_amount');

        return view('dashboard.print', compact(
            'transactions',
            'rangeLabel',
            'totalRevenue',
            'cashRevenue',
            'qrisRevenue',
            'startDate',
            'endDate'
        ));
    }
}
