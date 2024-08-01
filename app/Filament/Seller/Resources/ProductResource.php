<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Actions\ActionGroup;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationBadgeTooltip = 'Number of Products';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('seller_id', Auth::user()->id)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('seller_id', Auth::user()->id)
            )
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label("Product Name")
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('base_price')
                        ->label("Base Price")
                        ->money('PHP')
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('stock')
                        ->label("Stock")
                        ->sortable()
                        ->searchable(),
                ])
            ])
            ->contentGrid([
                'sm' => 2,
                'md' => 4,
                'xl' => 6,
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
