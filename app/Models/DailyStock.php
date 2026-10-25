<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStock extends Model
{
    protected $fillable = [
        'menu_id',
        'date',
        'quantity',
        'minimum_stock',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
