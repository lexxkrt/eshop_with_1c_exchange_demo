<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_name',
    'customer_email',
    'customer_phone',
    'address',
    'total',
    'status',
    'external_id',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total' => MoneyCast::class,
            'exported_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function totalRub(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' ₽';
    }
}
