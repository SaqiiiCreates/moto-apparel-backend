<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Product\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');

        if (! $query) {
            return response()->json([
                'data' => [],
                'total' => 0
            ]);
        }

        $products = Product::with([
            'images',
            'variants',
            'variants.images',
            'variants.attribute_values.attribute',
            'variants.product_flats',
            'product_flats',
        ])
        ->where('parent_id', null)
        ->whereHas('product_flats', function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('short_description', 'like', "%{$query}%");
        })
        ->limit(20)
        ->get();

        $data = [];

        foreach ($products as $product) {

            $flat = $product->product_flats->first();
            $mainImage = $product->images->first();

            $colorVariants = $product->variants->filter(function ($variant) {

                // safer: make sure relation exists
                if (! $variant->relationLoaded('attribute_values')) {
                    return false;
                }

                return $variant->attribute_values->contains(
                    fn ($a) => $a->attribute?->code === 'color'
                );
            });

            foreach ($colorVariants as $variant) {

                $variantFlat = $variant->product_flats
                    ->where('channel', core()->getRequestedChannelCode())
                    ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                    ->first();

                $variantImage = $variant->images->first();
                $parentImage = $product->images->first();

                $data[] = [
                    'id' => $variant->id,
                    'title' => $product->getAttribute('name') . ' - ' . $variant->name,
                    'slug' => $product->product_flats->first()?->url_key,

                    'price' => $variantFlat?->price
                        ?? $product->product_flats->first()?->price,

                    'image' => $variantImage
                        ? Storage::url($variantImage->path)
                        : ($parentImage ? Storage::url($parentImage->path) : null),

                    'product_id' => $product->id,

                    'color' => $variant->name,
                ];
            }

            // fallback: if no color variants, still show product
            if ($colorVariants->isEmpty()) {
                $data[] = [
                    'id' => $product->id,
                    'title' => $product->getAttribute('name'),
                    'slug' => $product->product_flats->first()?->url_key,
                    'price' => $flat?->price,
                    'image' => $mainImage ? Storage::url($mainImage->path) : null,
                    'product_id' => $product->id,
                ];
            }
        }

        return response()->json([
            'data' => $data,
            'total' => count($data)
        ]);
    }
}