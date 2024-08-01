<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $title = 'Dashboard';
    protected ?string $subheading = 'Subheading';
    public string $status;

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }
}