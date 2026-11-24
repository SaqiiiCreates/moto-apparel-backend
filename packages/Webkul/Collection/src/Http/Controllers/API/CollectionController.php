<?php

namespace Webkul\Collection\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Collection\Repositories\CollectionRepository;

class CollectionController extends Controller
{
    public function __construct(protected CollectionRepository $collectionRepository) {}

    /**
     * Get only popular collections with thumbnail
     */
    public function index()
    {
        // Fetch only popular collections and select id, name, slug, thumbnail_image
        $collections = $this->collectionRepository
            ->scopeQuery(fn($query) => $query->where('is_popular', 1)->where('is_active', 1)
                ->select(['id', 'name', 'slug', 'description', 'thumbnail_image']))
            ->all();

        // Transform thumbnail_image to full URL
        $collections = collect($collections)->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'thumbnail_image' => $c->thumbnail_image 
                ? asset('storage/' . $c->thumbnail_image) 
                : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $collections,
        ]);
    }

    /**
     * Show a single collection by slug
     */
    public function show($slug)
    {
        $collection = $this->collectionRepository
            ->with([
                'tabs',
                'tabs.categories',
            ])
            ->findWhere(['slug' => $slug])
            ->first();

        if (! $collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'sort_order' => $collection->sort_order,

                'thumbnail_image' => $collection->thumbnail_image
                    ? asset('storage/' . $collection->thumbnail_image)
                    : null,

                'banner_image' => $collection->banner_image
                    ? asset('storage/' . $collection->banner_image)
                    : null,

                'tabs' => $collection->tabs->map(function ($tab) {
                    $categoryIds = $tab->categories->pluck('id');

                    $products = \Webkul\Product\Models\Product::with([
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

                        // Parent products assigned to this category
                        $query->where(function ($q) use ($categoryIds) {
                            $q->whereNull('parent_id')
                            ->whereHas('categories', function ($q2) use ($categoryIds) {
                                $q2->whereIn('category_id', $categoryIds);
                            });
                        })

                        // OR variants assigned directly, but ONLY if parent is NOT assigned
                        ->orWhere(function ($q) use ($categoryIds) {
                            $q->whereNotNull('parent_id')
                            ->whereHas('categories', function ($q2) use ($categoryIds) {
                                $q2->whereIn('category_id', $categoryIds);
                            })
                            ->whereDoesntHave('parent.categories', function ($q3) use ($categoryIds) {
                                $q3->whereIn('category_id', $categoryIds);
                            });
                        });

                    })
                    ->take(20)
                    ->get();

                    return [
                        'id' => $tab->id,
                        'name' => $tab->name,
                        'slug' => $tab->slug,
                        'description' => $tab->description,
                        'is_active' => (bool) $tab->is_active,
                        'products' => $products->map(function ($p) {

                            $isVariant = ! is_null($p->parent_id);

                            $variantAttributes = $isVariant
                                ? $p->attribute_values
                                    ->filter(function ($attributeValue) {
                                        return $attributeValue->attribute
                                            && in_array($attributeValue->attribute->code, ['color', 'size']);
                                    })
                                    ->map(function ($attributeValue) {

                                        if (
                                            $attributeValue->attribute->type === 'select'
                                            && ! is_null($attributeValue->integer_value)
                                        ) {
                                            $value = optional(
                                                $attributeValue->attribute
                                                    ->options
                                                    ->firstWhere('id', $attributeValue->integer_value)
                                            )->label;
                                        } else {
                                            $value = $attributeValue->text_value
                                                ?? $attributeValue->integer_value
                                                ?? $attributeValue->float_value
                                                ?? $attributeValue->boolean_value
                                                ?? $attributeValue->datetime_value;
                                        }

                                        return [
                                            'code'  => $attributeValue->attribute->code,
                                            'name'  => $attributeValue->attribute->name,
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

                                $hasSize = $variant->attribute_values->contains(
                                    fn ($a) => $a->attribute?->code === 'size'
                                );

                                $type = $hasColor
                                    ? 'color'
                                    : ($hasSize ? 'size' : 'unknown');

                                $images = $variant->images->map(fn ($img) => [
                                    'id' => $img->id,
                                    'url' => \Storage::url($img->path),
                                ]);

                                return [
                                    'id' => $variant->id,
                                    'name' => $variant->name,
                                    'price' => $variantFlat?->price,
                                    'type' => $type,

                                    'images' => $type === 'size'
                                        ? (
                                            $p->images->first()
                                                ? [[
                                                    'id' => $p->images->first()->id,
                                                    'url' => \Storage::url($p->images->first()->path),
                                                ]]
                                                : []
                                        )
                                        : $images,
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
                        })
                    ];
                }),
            ],
        ]);
    }
}