<?php

namespace App\Filament\Seller\Resources\ProductResource\Pages;

use App\Filament\Seller\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;

class EditProduct extends EditRecord
{
    use EditRecord\Concerns\HasWizard;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Product updated successfully!';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $result = [];
        $categories = $data['variants']['categories'];

        foreach ($categories as $type => $variants) {
            $result[] = [
                'type' => $type,
                'variants' => $variants
            ];
        }

        $data['variants']['categories'] = $result;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Fix the casts of the variants.details values
        foreach ($data['variants']['details'] as &$detail) {
            $detail['add_price'] = floatval($detail['add_price']);
            $detail['total_price'] = floatval($detail['total_price']);
            $detail['stock'] = intval($detail['stock']);
        }
        unset($detail);

        $data['base_price'] = floatval($data['base_price']);


        // Mutate variants.categories
        $categories = $data['variants']['categories'];
        $result = [];
        foreach ($categories as $category) {
            if (isset($category['type']) && isset($category['variants'])) {
                $result[$category['type']] = $category['variants'];
            }
        }

        $data['variants']['categories'] = $result;

        return $data;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Product Name')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->label("Product Name"),
                    TextInput::make('base_price')
                        ->required()
                        ->label("Base Price"),
                ]),
            Step::make('Variants')
                ->schema([
                    Section::make('Variants')
                        ->schema([
                            Repeater::make('variants.categories')
                                ->schema([
                                    TextInput::make('type')
                                        ->required()
                                        ->label("Variant Type")
                                        ->placeholder("Add variant types (e.g., Color, Size, Texture)")
                                        ->distinct()
                                        ->live(onBlur: true),
                                    TagsInput::make('variants')
                                        ->splitKeys(['Tab'])
                                        ->hint("Press 'Enter' or 'Tab' key to add variants")
                                        ->placeholder(function (Get $get) {
                                            $category = $get('type');
                                            return $category ? 'New ' . strtolower($category) . ' variant' : 'New variant (e.g., Red, Green, Blue)';
                                        })
                                        ->reorderable(),
                                ])
                                ->label('Variant Types')
                                ->minItems(1)
                                ->columns(2)
                                ->addActionLabel('Add Variant Type')
                                ->itemLabel(fn(array $state): ?string => $state['type'] ?? null)
                                ->reorderable()
                                ->cloneable()
                                ->collapsible(),
                        ]),
                ]),
            Step::make('Variant Details')
                ->schema(
                    function ($state) {
                        $variants = [];
                        foreach ($state['variants']['categories'] as $item) {
                            $variants[] = $item['variants'];
                        }

                        // Create $variant_combos
                        $numArrays = count($variants);
                        $indices = array_fill(0, $numArrays, 0);
                        $variant_combos = [];
                        while (true) {
                            $combination = [];
                            for ($i = 0; $i < $numArrays; $i++) {
                                if (!isset($variants[$i][$indices[$i]])) {
                                    return [];
                                }

                                $combination[] = $variants[$i][$indices[$i]];
                            }
                            $variant_combos[] = implode(', ', $combination);

                            $increment = true;
                            for ($i = $numArrays - 1; $i >= 0; $i--) {
                                $indices[$i]++;
                                if ($indices[$i] < count($variants[$i])) {
                                    $increment = false;
                                    break;
                                }
                                $indices[$i] = 0;
                            }
                            if ($increment) {
                                break;
                            }
                        }

                        // Create components
                        $components = [];
                        foreach ($variant_combos as $variant) {
                            $base_price = $state['base_price'];
                            $components[] =
                                Section::make($variant)
                                    ->schema([
                                        TextInput::make('variants.' . 'details.' . $variant . '.add_price')
                                            ->label('Additional Price')
                                            ->prefix('₱')
                                            ->helperText('Additional price for this variant that will be added to the base price of this product. If left empty, the value will be zero.')
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->minValue(0)
                                            ->maxValue(999999)
                                            ->placeholder(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(
                                                fn(Set $set, $state) =>
                                                $set('variants.' . 'details.' . $variant . '.total_price', floatval($state + $base_price))
                                            ),
                                        TextInput::make('variants.' . 'details.' . $variant . '.total_price')
                                            ->label('Total Price')
                                            ->prefix('₱')
                                            ->helperText('Formula: Base Price + Additional Price')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->placeholder(floatval($state['base_price'])),
                                        TextInput::make('variants.' . 'details.' . $variant . '.stock')
                                            ->label('Stock')
                                            ->helperText('Current stock of this variant. If left empty, the value will be zero.')
                                            ->integer()
                                            ->inputMode('numeric')
                                            ->minValue(0)
                                            ->maxValue(999999)
                                            ->placeholder(0),
                                    ])
                                    ->collapsible()
                                    ->columns(3);
                        }
                        return $components;
                    }
                ),
            Step::make('Description')
                ->schema([
                    RichEditor::make('description'),
                ]),
        ];
    }
}
