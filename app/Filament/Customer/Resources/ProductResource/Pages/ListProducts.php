<?php

namespace App\Filament\Customer\Resources\ProductResource\Pages;

use App\Filament\Customer\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->icon('heroicon-m-user-group'),
            'flash-sale' => Tab::make('Flash Sale')
                ->icon('heroicon-m-user-group')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('name', 'Seller')),
            'categories' => Tab::make('Categories')
                ->icon('heroicon-m-user-group'),
            'just-for-you' => Tab::make('Just For You')
                ->icon('heroicon-m-user-group'),
            'top-up' => Tab::make('Top Up')
                ->icon('heroicon-m-user-group'),
            'vouchers' => Tab::make('Vouchers')
                ->icon('heroicon-m-user-group'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'Flash Sale';
    }
}
