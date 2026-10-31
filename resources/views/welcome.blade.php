<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>{{ config('shop.store.name', config('app.name', 'Footwear Store')) }} — Bangladesh Women's Shoes</title>
    <meta name="description" content="Mobile-first eCommerce store for women's footwear in Bangladesh. Authentic handcrafted shoes, heels, and flats with delivery across all 64 districts.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full font-['Plus_Jakarta_Sans',sans-serif] text-slate-900 antialiased selection:bg-rose-500 selection:text-white">
    <!-- Mobile-First Navigation Header -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-rose-600 to-pink-500 text-white shadow-sm shadow-rose-500/20">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-base font-extrabold tracking-tight text-slate-900">{{ config('shop.store.name', 'Heel Bari') }}</span>
                    <span class="block text-[10px] font-medium tracking-wide text-rose-600 uppercase">Dhaka, Bangladesh</span>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-2">
                <a href="{{ url('/admin') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 transition">
                    <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Filament Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <!-- Hero Section -->
        <div class="text-center sm:py-6">
            <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                <span class="h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                Mobile-First Women's Footwear eCommerce Foundation
            </div>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                Ready for <span class="bg-gradient-to-r from-rose-600 via-pink-600 to-rose-700 bg-clip-text text-transparent">Bangladeshi Shoppers</span>
            </h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm sm:text-base text-slate-600 leading-relaxed">
                Clean, resilient Laravel architecture featuring Livewire v3, Tailwind CSS with Vite, Alpine.js, Filament admin panel, Spatie MediaLibrary, and strict server-side BDT recalculation.
            </p>
        </div>

        <!-- Livewire & Alpine Interactive Showcase -->
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <!-- Left Card: Livewire Component -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-purple-100 px-2 py-1 text-xs font-bold text-purple-700">Livewire 3</span>
                        <h2 class="text-sm font-bold text-slate-800">Dynamic Price Calculation</h2>
                    </div>
                    <span class="text-xs text-slate-500">Realtime Reactive</span>
                </div>

                <livewire:welcome-demo />

                <p class="mt-3 text-[11px] text-slate-500 leading-normal">
                    Adhering to rule: <em>"Never trust client-side prices, stock or totals. Recalculate everything on the server."</em>
                </p>
            </div>

            <!-- Right Card: Alpine.js Interactive UI Demo -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs" x-data="{ selectedSize: '38', color: 'rose', inStock: true }">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-sky-100 px-2 py-1 text-xs font-bold text-sky-700">Alpine.js</span>
                        <h2 class="text-sm font-bold text-slate-800">Mobile Touch Variant Picker</h2>
                    </div>
                    <span class="text-xs text-emerald-600 font-semibold" x-text="inStock ? '● In Stock' : '○ Pre-order'"></span>
                </div>

                <div class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <!-- Size Selector -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-2">
                            <span>Select Shoe Size (EU):</span>
                            <span class="text-rose-600" x-text="'Size: ' + selectedSize"></span>
                        </div>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="size in ['36', '37', '38', '39', '40']" :key="size">
                                <button
                                    type="button"
                                    @click="selectedSize = size"
                                    :class="selectedSize === size ? 'bg-rose-600 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:border-rose-300'"
                                    class="h-10 rounded-lg text-sm transition"
                                    x-text="size"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-2">
                            <span>Available Colors:</span>
                            <span class="capitalize text-slate-500" x-text="color"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="color = 'rose'"
                                :class="color === 'rose' ? 'ring-2 ring-rose-600 ring-offset-2' : ''"
                                class="h-8 w-8 rounded-full bg-rose-500 transition"
                            ></button>
                            <button
                                type="button"
                                @click="color = 'black'"
                                :class="color === 'black' ? 'ring-2 ring-slate-900 ring-offset-2' : ''"
                                class="h-8 w-8 rounded-full bg-slate-900 transition"
                            ></button>
                            <button
                                type="button"
                                @click="color = 'nude'"
                                :class="color === 'nude' ? 'ring-2 ring-amber-600 ring-offset-2' : ''"
                                class="h-8 w-8 rounded-full bg-amber-200 transition"
                            ></button>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-[11px] text-slate-500 leading-normal">
                    Smooth mobile touch targets designed for Bangladesh mobile shoppers.
                </p>
            </div>
        </div>

        <!-- Stack & Configuration Grid -->
        <div class="mt-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">System & Stack Verification</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <div class="text-[11px] font-medium text-slate-400">Framework</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">Laravel {{ app()->version() }}</div>
                    <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">PHP {{ phpversion() }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <div class="text-[11px] font-medium text-slate-400">Database</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">MySQL</div>
                    <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Connected ({{ config('database.connections.mysql.database') }})</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <div class="text-[11px] font-medium text-slate-400">Currency & Timezone</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">{{ config('shop.currency.code') }} ({{ config('shop.currency.symbol') }})</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ config('app.timezone') }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs">
                    <div class="text-[11px] font-medium text-slate-400">Admin Panel</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">Filament v3</div>
                    <div class="text-[10px] text-indigo-600 font-semibold mt-0.5">Route: /admin</div>
                </div>
            </div>
        </div>

        <!-- Installed Packages Checklist -->
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Configured Modules & Services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs text-slate-700">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>spatie/laravel-medialibrary</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>barryvdh/laravel-debugbar</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>Vite + Tailwind CSS</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>app/Services</strong> &amp; <strong>app/Actions</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>Model::preventLazyLoading</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>Redis Config Ready</strong></span>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>
