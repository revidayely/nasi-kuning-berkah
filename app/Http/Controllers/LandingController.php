<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->with(['category', 'dailyStocks' => fn ($query) => $query->where('date', today()->toDateString())])
            ->where('is_available', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->whereHas('menus')
            ->orderBy('name')
            ->get();

        return view('landing.index', compact('menus', 'categories'));
    }

    public function menu(): View
    {
        return $this->index();
    }

    public function cart(): View
    {
        return view('landing.cart');
    }
}
