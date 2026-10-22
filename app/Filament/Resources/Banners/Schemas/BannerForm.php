<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero Slide Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('badge')
                            ->label('Top Eyebrow Badge')
                            ->placeholder('e.g. FESTIVE COLLECTION 2026')
                            ->maxLength(60),
                        TextInput::make('title')
                            ->label('Slide Headline')
                            ->placeholder('e.g. Step Into Festive Elegance')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('subtitle')
                            ->label('Sub-headline / Description')
                            ->placeholder('Brief persuasive copy about comfort, festive drops, or materials...')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('button_text')
                            ->label('Call to Action Button Text')
                            ->default('Shop Collection')
                            ->required(),
                        TextInput::make('button_link')
                            ->label('Target Link')
                            ->default('#new-arrivals')
                            ->required(),
                    ]),

                Section::make('Design & Visuals')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Hero Shoe Visual (WebP / PNG)')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->directory('banners')
                            ->visibility('public'),
                        Select::make('bg_gradient')
                            ->label('Background Theme')
                            ->options([
                                'from-rose-950 via-neutral-900 to-black' => 'Royal Rose & Midnight Black',
                                'from-stone-900 via-amber-950 to-neutral-950' => 'Warm Amber & Earthy Stone',
                                'from-slate-950 via-zinc-900 to-neutral-900' => 'Modern Slate & Dark Silver',
                                'from-emerald-950 via-teal-950 to-black' => 'Deep Emerald & Black',
                            ])
                            ->default('from-rose-950 via-neutral-900 to-black'),
                        TextInput::make('sort_order')
                            ->label('Slide Position / Order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active on Storefront')
                            ->default(true),
                    ]),
            ]);
    }
}
