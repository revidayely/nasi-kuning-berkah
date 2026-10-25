<?php

namespace App\Http\Controllers;

use App\Models\DailyStock;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyStockController extends Controller
{
    public function index(): JsonResponse|View
    {
        $menus = Menu::query()
            ->with(['category', 'dailyStocks' => fn ($query) => $query->where('date', today()->toDateString())])
            ->orderBy('name')
            ->get();

        $stocks = $menus->map(function (Menu $menu) {
            $stock = $menu->dailyStocks->first();

            return (object) [
                'menu_id' => $menu->id,
                'menu' => $menu,
                'quantity' => $stock?->quantity ?? 0,
                'minimum_stock' => $stock?->minimum_stock ?? 0,
                'updated_at' => $stock?->updated_at,
            ];
        });

        if (request()->expectsJson()) {
            return response()->json($stocks);
        }

        return view('stock.index', ['stocks' => $stocks]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'stocks' => ['required', 'array'],
            'stocks.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'stocks.*.quantity' => ['required', 'integer', 'min:0'],
            'stocks.*.minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['stocks'] as $stock) {
            DailyStock::query()->updateOrCreate(
                [
                    'menu_id' => $stock['menu_id'],
                    'date' => today()->toDateString(),
                ],
                [
                    'quantity' => $stock['quantity'],
                    'minimum_stock' => $stock['minimum_stock'],
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Stok harian berhasil disimpan.']);
        }

        return redirect()->route('stock.index')->with('success', 'Stok harian berhasil disimpan.');
    }
}
