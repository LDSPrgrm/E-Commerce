<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\ProductResource\Pages;
use App\Filament\Customer\Resources\ProductResource\RelationManagers;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\View\Components\Modal;
use Filament\Infolists\Components\Split;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                // Tables\Actions\ViewAction::make()
                //     ->modalCancelAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Split::make([
                            Section::make([
                                TextEntry::make('')
                                    ->label('Preview Here'),
                                TextEntry::make('')
                                    ->label('Images Here'),
                            ]),
                        ]),
                        Split::make([
                            TextEntry::make('name'),
                            TextEntry::make('price'),
                            TextEntry::make('stock'),
                            TextEntry::make('description')
                                ->formatStateUsing(fn(string $state): HtmlString => new HtmlString($state)),
                        ]),
                        Split::make([
                            Section::make([
                                TextEntry::make('created_at')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                        ]),
                    ]),
                Grid::make(2)
                    ->schema([
                        Actions::make([]),
                        Actions::make([
                            Action::make('add to cart')
                                ->label('Add To Cart')
                                ->icon('heroicon-m-shopping-cart')
                                ->form([
                                    Hidden::make('product_id')
                                        ->required()
                                        ->default(fn(Product $product): string => $product->id),
                                    Hidden::make('product_name')
                                        ->required()
                                        ->default(fn(Product $product): string => $product->name),
                                    Repeater::make('variant_types')
                                        ->label('Variant')
                                        ->schema(function (Product $product) {
                                            $components = [];
                                            foreach ($product->variant_types as $i => $variant_type) {
                                                $components[$i] = Forms\Components\Radio::make($product->variant_types[$i]['types'])
                                                    ->required()
                                                    ->label(fn(Product $product) => $product->variant_types[$i]['types'])
                                                    ->options(function (Product $product) use ($i) {
                                                        $product = Product::find($product->id);

                                                        $options = [];
                                                        foreach ($product->variant_types[$i]['variants'] as $variant) {
                                                            $options[$variant['name']] = $variant['name'];
                                                        }

                                                        return $options;
                                                    })
                                                    ->live()
                                                    ->afterStateUpdated(
                                                        function (Product $product, Set $set) use ($i) {
                                                            $set('max_quantity', $product->variant_types[$i]['variants'][$i]['stock']);
                                                        }
                                                    );
                                            }

                                            return $components;
                                        })
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false),
                                    Hidden::make('product_price')
                                        ->required()
                                        ->default(fn(Product $product): float => $product->price),
                                    TextInput::make('max_quantity')
                                        ->default(1)
                                        ->live(),
                                    TextInput::make('quantity')
                                        ->required()
                                        ->integer()
                                        ->default(1)
                                        ->minValue(1)
                                        ->maxValue(fn(Get $get): int => intval($get('max_quantity'))),
                                    Hidden::make('customer_id')
                                        ->required()
                                        ->default(Auth::user()->id),
                                    Hidden::make('seller_id')
                                        ->required()
                                        ->default(fn(Product $product): string => $product->seller_id),
                                ])
                                ->stickyModalHeader()
                                ->action(function (array $data) {
                                    $cart = Cart::updateOrCreate(
                                        [
                                            'customer_id' => $data['customer_id'],
                                            'product_id' => $data['product_id'],
                                            'variant' => $data['variant_types'],
                                        ],
                                        [
                                            'customer_id' => $data['customer_id'],
                                            'product_id' => $data['product_id'],
                                            'seller_id' => $data['seller_id'],
                                            'product_name' => $data['product_name'],
                                            'variant' => $data['variant_types'],
                                            'product_price' => $data['product_price'],
                                        ]
                                    );
                                    $cart->quantity += $data['quantity'];
                                    $cart->save();
                                }),
                            Action::make('checkout')
                                ->label('Checkout')
                                ->icon('heroicon-m-shopping-bag')
                                ->steps([
                                    Step::make('Order')
                                        ->icon('heroicon-m-shopping-bag')
                                        ->schema([
                                            TextInput::make('product name')
                                                ->required()
                                                ->label('Product Name'),
                                            Forms\Components\Radio::make('variant')
                                                ->required()
                                                ->label('Variant')
                                                ->options([
                                                    'Black' => 'Black',
                                                    'Gray' => 'Gray',
                                                    'White' => 'White',
                                                ]),
                                            TextInput::make('quantity')
                                                ->required()
                                                ->numeric()
                                                ->default(1),
                                        ]),
                                    Step::make('Delivery')
                                        ->icon('heroicon-m-truck')
                                        ->schema([
                                            Forms\Components\Checkbox::make('')
                                                ->label('Use my default address')
                                                ->default(true)
                                        ]),
                                    Step::make('Payment')
                                        ->icon('heroicon-m-credit-card')
                                        ->schema([
                                            // ...
                                        ]),
                                ])
                                ->action(function (array $data): void {
                                }),
                        ])
                            ->fullWidth(),
                    ]),
            ]);
    }
}
