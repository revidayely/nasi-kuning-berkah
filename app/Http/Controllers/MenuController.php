<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\Category;
use App\Models\DailyStock;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Menu::query()->with('category', 'dailyStocks')->latest()->get()
        );
    }

    public function manage(): View
    {
        $menus = Menu::query()
            ->with(['category', 'dailyStocks' => fn ($query) => $query->where('date', today()->toDateString())])
            ->orderBy('name')
            ->get();

        return view('menus.manage', [
            'categories' => Category::query()->orderBy('name')->get(),
            'menus' => $menus,
        ]);
    }

    public function staffIndex(): View
    {
        $menus = Menu::query()
            ->with(['category', 'dailyStocks' => fn ($query) => $query->where('date', today()->toDateString())])
            ->orderBy('name')
            ->get();

        return view('menus.staff-index', compact('menus'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'required_without:category_name', 'integer', 'exists:categories,id'],
            'category_name' => ['nullable', 'required_without:category_id', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['boolean'],
        ]);
        $validated['is_available'] = $request->boolean('is_available');

        $menu = DB::transaction(function () use ($validated): Menu {
            if (! empty($validated['category_id'])) {
                $category = Category::query()->findOrFail($validated['category_id']);
            } else {
                $categoryName = trim($validated['category_name']);
                $category = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );
            }

            $slug = Str::slug($validated['name']);
            if (Menu::query()->where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(5));
            }

            $menu = Menu::query()->create([
                'category_id' => $category->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'image_url' => $validated['image_url'] ?? null,
                'is_available' => $validated['is_available'],
            ]);

            DailyStock::query()->updateOrCreate(
                ['menu_id' => $menu->id, 'date' => today()->toDateString()],
                [
                    'quantity' => $validated['initial_stock'] ?? 0,
                    'minimum_stock' => $validated['minimum_stock'] ?? 0,
                ]
            );

            return $menu;
        });

        if ($request->expectsJson()) {
            return response()->json($menu->load('category', 'dailyStocks'), 201);
        }

        return redirect()->route('menus.manage')->with('success', "Menu {$menu->name} berhasil ditambahkan beserta stok awal hari ini.");
    }

    public function update(Request $request, Menu $menu): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        unset($validated['image']);
        if ($request->hasFile('image')) {
            $validated['image_url'] = Storage::disk('public')->url($request->file('image')->store('menus', 'public'));
        }

        $menu->update($validated);

        if ($request->expectsJson()) {
            return response()->json($menu->fresh()->load('category', 'dailyStocks'));
        }

        return redirect()->route('menus.manage')->with('success', "Data menu {$menu->name} berhasil diperbarui.");
    }
}
