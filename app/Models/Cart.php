<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Casts\FloatCast;
use App\Casts\IntegerCast;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'seller_id',
        'product_id',
        'product_name',
        'variant',
        'product_price',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'product_price' => FloatCast::class,
            'quantity' => IntegerCast::class,
        ];
    }
}
