@extends('layouts.storefront')

@section('title', 'All Women\'s Footwear Catalog | ' . ($storeSettings['shop_name'] ?? '[BRAND_NAME]'))
@section('meta_description', 'Explore all handcrafted footwear in Bangladesh: festive block heels, kitten heels, memory foam flats, suede Boston clogs, and ethnic Kolhapuris. Cash on Delivery across 64 districts.')

@section('og_title', 'All Women\'s Footwear Collection')
@section('og_description', 'Browse handcrafted women\'s footwear in Bangladesh with cloud-cushioned footbeds and 7-day size exchange.')
@section('og_image', asset('images/banners/hero-heels.webp'))

@section('content')
    <livewire:product-listing />
@endsection
