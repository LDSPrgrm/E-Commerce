<?php

namespace App\Filament\Seller\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $title = 'Seller';
    protected ?string $subheading = 'Subheading';
    public string $status;

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }
}