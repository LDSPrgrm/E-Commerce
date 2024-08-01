<?php

namespace App\Filament\Customer\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $navigationLabel = 'Home';
    protected static ?string $title = 'Customer';
    protected ?string $subheading = 'Subheading';
    public string $status;

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }
}