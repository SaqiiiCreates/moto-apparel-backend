<?php

namespace Webkul\Category\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\Product;

class CategoryShowController extends Controller
{
    public function __construct(protected CategoryRepository $categoryRepository) {}

    public function show($slug)
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if (! $category || (int) $category->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $categoryIds = collect([$category->id]);

        $products = Product::with([
            'images',
            'inventories',
            'variants',
            'variants.images',
            'variants.product_flats',
            'variants.attribute_values.attribute.options',
            'variants.inventories',
            'product_flats',
            'categories',
            'attribute_values.attribute.options',
            'parent.images',
            'parent.categories',
        ])
        ->where(function ($query) use ($categoryIds) {
            $query->where(function ($q) use ($categoryIds) {
                $q->whereNull('parent_id')
                    ->whereHas('categories', function ($q2) use ($categoryIds) {
                        $q2->whereIn('categories.id', $categoryIds);
                    });
            })
            ->orWhere(function ($q) use ($categoryIds) {
                $q->whereNotNull('parent_id')
                    ->whereHas('categories', function ($q2) use ($categoryIds) {
                        $q2->whereIn('categories.id', $categoryIds);
                    })
                    ->whereDoesntHave('parent.categories', function ($q3) use ($categoryIds) {
                        $q3->whereIn('categories.id', $categoryIds);
                    });
            });
        })
        ->take(50)
        ->get();

        $mapped = $products->map(function ($p) {
            $isVariant = ! is_null($p->parent_id);

            $variantAttributes = $isVariant
                ? $p->attribute_values
                    ->filter(fn ($av) => $av->attribute && in_array($av->attribute->code, ['color', 'size']))
                    ->map(function ($av) {
                        if ($av->attribute->type === 'select' && ! is_null($av->integer_value)) {
                            $value = optional(
                                $av->attribute->options->firstWhere('id', $av->integer_value)
                            )->label;
                        } else {
                            $value = $av->text_value ?? $av->integer_value ?? $av->float_value;
                        }

                        return [
                            'code'  => $av->attribute->code,
                            'name'  => $av->attribute->name,
                            'value' => $value,
                        ];
                    })
                    ->values()
                : [];

            $flat = $p->product_flats
                ->where('channel', core()->getRequestedChannelCode())
                ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                ->first();

            $minPrice = $p->variants
                ->map(function ($variant) {
                    return $variant->product_flats
                        ->where('channel', core()->getRequestedChannelCode())
                        ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                        ->first()?->price;
                })
                ->filter()
                ->min();

            $colors = $p->variants->map(function ($variant) use ($p) {
                $variantFlat = $variant->product_flats
                    ->where('channel', core()->getRequestedChannelCode())
                    ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                    ->first();

                $hasColor = $variant->attribute_values->contains(
                    fn ($a) => $a->attribute?->code === 'color'
                );

                $images = $variant->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => \Storage::url($img->path),
                ]);

                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => $variantFlat?->price,
                    'type' => $hasColor ? 'color' : 'size',
                    'images' => $images,
                ];
            });

            return [
                'id' => $p->id,
                'slug' => $p->url_key,
                'parent_id' => $p->parent_id,
                'type' => $p->type,
                'sku' => $p->sku,
                'name' => $p->name,
                'price' => $flat?->price ?? $minPrice,
                'image' => $p->images->first()
                    ? \Storage::url($p->images->first()->path)
                    : (
                        $isVariant && $p->parent && $p->parent->images->first()
                            ? \Storage::url($p->parent->images->first()->path)
                            : null
                    ),
                'colors' => $isVariant ? [] : $colors,
                'is_variant' => $isVariant,
                'variant_attributes' => $variantAttributes,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'banner_image' => $category->banner_path
                    ? asset('storage/' . $category->banner_path)
                    : ($category->banner_url ?? null),
                'logo_image' => $category->logo_path
                    ? asset('storage/' . $category->logo_path)
                    : ($category->logo_url ?? null),
                'products' => $mapped,
            ],
        ]);
    }
}