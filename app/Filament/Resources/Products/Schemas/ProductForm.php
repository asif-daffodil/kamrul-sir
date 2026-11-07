<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Product Details')
                    ->description('Primary product metadata and pricing')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('base_price')
                            ->label('Base Price (BDT)')
                            ->required()
                            ->numeric()
                            ->prefix('৳')
                            ->default(0.00),
                        Toggle::make('is_featured')
                            ->label('Featured Product')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Published in Store')
                            ->default(true),
                        Textarea::make('short_description')
                            ->rows(2)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Product Gallery')
                    ->description('Multiple image upload with WebP conversion')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->columns(4)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image File')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->directory('products')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('1200')
                                    ->required(),
                                TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('Image description'),
                                Toggle::make('is_primary')
                                    ->label('Cover Image')
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->defaultItems(1)
                            ->reorderable('sort_order'),
                    ]),

                Section::make('Product Variants (SKUs, Colors, Sizes & Stock)')
                    ->description('Color, shoe sizes, SKUs, inventory stock and promotional sale prices')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->columns(4)
                            ->schema([
                                TextInput::make('color')
                                    ->label('Color Name')
                                    ->placeholder('e.g. Midnight Black')
                                    ->required(),
                                ColorPicker::make('color_code')
                                    ->label('Color Swatch'),
                                Select::make('size')
                                    ->label('Size (EU)')
                                    ->options([
                                        '35' => 'EU 35',
                                        '36' => 'EU 36',
                                        '37' => 'EU 37',
                                        '38' => 'EU 38',
                                        '39' => 'EU 39',
                                        '40' => 'EU 40',
                                        '41' => 'EU 41',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->placeholder('e.g. WSH-HEEL-001-38'),
                                TextInput::make('price')
                                    ->label('Regular Price (৳)')
                                    ->numeric()
                                    ->prefix('৳')
                                    ->required(),
                                TextInput::make('sale_price')
                                    ->label('Sale Price (৳)')
                                    ->numeric()
                                    ->prefix('৳')
                                    ->nullable(),
                                TextInput::make('stock')
                                    ->label('Stock Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->itemLabel(fn (array $state): ?string => ($state['color'] ?? '') . ' - Size ' . ($state['size'] ?? '') . ' (Stock: ' . ($state['stock'] ?? '0') . ')')
                            ->collapsible()
                            ->defaultItems(1),
                    ]),

                Section::make('Search Engine Optimization (SEO)')
                    ->description('Metadata for Google search ranking and social sharing previews')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(60)
                            ->placeholder('e.g. Buy Handcrafted Velvet Block Heels in Bangladesh'),
                        Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(160)
                            ->placeholder('Concise summary for search engine snippet'),
                        TextInput::make('meta_keywords')
                            ->placeholder('shoes, heels, bangladesh, women footwear'),
                    ]),
            ]);
    }
}
