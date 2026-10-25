<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'cashier_id',
        'status',
        'payment_method',
        'payment_status',
        'kitchen_status',
        'receipt_image',
        'paid_at',
        'expires_at',
        'total_amount',
        'received_amount',
        'change_amount',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'received_amount' => 'integer',
        'change_amount' => 'integer',
        'cashier_id' => 'integer',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
