<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $customers = User::where('role', 'customer')->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $imageGen = new \App\Services\PlaceholderImageGenerator();
        foreach ($customers as $cust) {
            $slug = \Illuminate\Support\Str::slug($cust->name);
            $avRel = "images/avatars/{$slug}.webp";
            $avFull = public_path($avRel);
            $avStorage = storage_path("app/public/avatars/{$slug}.webp");

            $imageGen->generateReviewAvatar($cust->name, 200, $avFull);
            if (! is_dir(dirname($avStorage))) {
                mkdir(dirname($avStorage), 0755, true);
            }
            copy($avFull, $avStorage);

            $cust->clearMediaCollection('avatars');
            if (file_exists($avFull)) {
                $cust->addMedia($avFull)->preservingOriginal()->toMediaCollection('avatars');
            }
        }

        $reviewsPool = [
            [
                'rating' => 5,
                'comment' => 'The velvet block heels are unbelievably comfortable! Wore them through a 6-hour wedding reception with zero foot pain.',
                'approved' => true,
            ],
            [
                'rating' => 5,
                'comment' => 'Delivered to Dhanmondi within 24 hours. The packaging felt like unboxing a luxury designer piece. 10/10.',
                'approved' => true,
            ],
            [
                'rating' => 4,
                'comment' => 'Beautiful finish and authentic leather smell. Size 38 fits true to size for wide feet.',
                'approved' => true,
            ],
            [
                'rating' => 5,
                'comment' => 'The arch support is magical. The kitten heels are now my daily office staple.',
                'approved' => true,
            ],
            [
                'rating' => 4,
                'comment' => 'Loved the craftsmanship on the Kolhapuri chappal. Very graceful embroidery.',
                'approved' => true,
            ],
            [
                'rating' => 5,
                'comment' => 'Boston clogs look exactly like high-end European imports. Quality is phenomenal!',
                'approved' => true,
            ],
            [
                'rating' => 3,
                'comment' => 'Quality is decent, but courier took 4 days to reach Chattogram.',
                'approved' => false, // pending approval
            ],
        ];

        foreach ($products->take(12) as $product) {
            $sampleReview = fake()->randomElement($reviewsPool);
            $customer = $customers->random();

            Review::create([
                'product_id' => $product->id,
                'user_id' => $customer->id,
                'rating' => $sampleReview['rating'],
                'comment' => $sampleReview['comment'],
                'is_approved' => $sampleReview['approved'],
                'approved_at' => $sampleReview['approved'] ? now()->subDays(rand(1, 15)) : null,
            ]);
        }
    }
}
