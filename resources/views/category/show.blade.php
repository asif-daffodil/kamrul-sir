@extends('layouts.storefront')

@section('title', ($category->meta_title ?? $category->name . ' Collection') . ' | ' . ($storeSettings['shop_name'] ?? 'Heel Bari'))
@section('meta_description', $category->meta_description ?? $category->description ?? ('Shop ' . $category->name . ' for women in Bangladesh. Handcrafted comfort with Cash on Delivery & 7 Days Exchange.'))

@section('og_title', $category->name . ' Footwear Collection')
@section('og_description', $category->description ?? ('Handcrafted ' . $category->name . ' engineered for cloud-walking comfort in Bangladesh.'))
@section('og_image', $category->image_url)
@section('canonical_url', route('category.show', $category->slug))

@section('content')
    <livewire:product-listing :category-slug="$category->slug" />
@endsection
