<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Casts\FloatCast;
use App\Casts\IntegerCast;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'name',
        'base_price',
        'variants',
        'description',
        'sold',
        'rating',
        'reviews',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => FloatCast::class,
            'rating' => FloatCast::class,
            'sold' => IntegerCast::class,
            'stock' => IntegerCast::class,
        ];
    }
}
