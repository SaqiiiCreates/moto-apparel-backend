<?php

namespace Webkul\Product\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Product\Models\Product;
use Webkul\Attribute\Models\AttributeOption;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Models\ProductDetail;

class ProductController extends Controller
{
    public function latest(): JsonResponse
    {        
        $products = Product::with([
            'images',
            'inventories',
            'variants',
            'variants.product_flats',
            'variants.attribute_values.attribute',
            'variants.images',
            'variants.inventories',
            'product_flats', 
            'categories',
            'categories.parent',
        ])
        ->where('parent_id', null)
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

        $data = $products->map(function($product) {
            
            $colors = $product->variants->map(function($variant) use ($product) {

            $variantFlat = $this->getFlat($variant);

            $price = $variantFlat?->price ?? null;

                if (!$price) {
                    $price = null;
                }

                // TYPE DETECTION
                $hasColor = $variant->attribute_values->contains(
                    fn ($a) => $a->attribute?->code === 'color'
                );

                $hasSize = $variant->attribute_values->contains(
                    fn ($a) => $a->attribute?->code === 'size'
                );

                if ($hasColor) {
                    $type = 'color';
                } elseif ($hasSize) {
                    $type = 'size';
                } else {
                    $type = 'unknown';
                }

                // THUMBNAIL IMAGE RULE
                $images = $variant->images->map(fn($img) => [
                    'id'  => $img->id,
                    'url' => Storage::url($img->path),
                ]);

                // FINAL RETURN
                return [
                    'name' => $variant->name,
                    'price' => $price,
                    'type'  => $type,

                    'hex'  => $variant->color_hex ?? null,

                    'stock' => [
                        'total' => $variant->inventories->sum('qty'),
                        'sources' => $variant->inventories->map(fn ($inv) => [
                            'inventory_source_id' => $inv->inventory_source_id,
                            'qty' => $inv->qty,
                        ]),
                    ],

                    // THUMBNAIL RULE:
                    // IF ONLY SIZE VARIANTS → SHOW ONLY PARENT IMAGE
                    'images' => $type === 'size'
                        ? ($product->images->first() ? [[
                            'id' => $product->images->first()->id,
                            'url' => Storage::url($product->images->first()->path),
                        ]] : [])
                        : $images,

                    'explore_outfit' => [],
                ];
            });

            $flat = $product->product_flats
                ->where('channel', core()->getRequestedChannelCode())
                ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                ->first();

            // PRODUCT PRICE RULE (SMALLEST VARIANT WINS)
            $minPrice = $product->variants
            ->map(function($v) {
                return $v->product_flats
                    ->where('channel', core()->getRequestedChannelCode())
                    ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
                    ->first()?->price;
            })
            ->filter(fn($p) => $p !== null)
            ->min();

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'slug' => $product->product_flats->first()?->url_key,
                'title' => $product->getAttribute('name'),
                'subtitle' => $product->getAttribute('short_description'),

                'price' => $flat?->price ?? $minPrice,

                'currency' => 'USD',
                'currency_symbol' => '$',

                'collection' => $product->categories->first()?->name ?? null,

                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'parent_id' => $category->parent_id,

                        'parent' => $category->parent ? [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                            'slug' => $category->parent->slug,
                        ] : null,
                    ];
                })->values(),

                'colors' => $colors,

                'model_info' => $product->model_info ?? null,
                'description' => $product->description,
                'composition' => $product->composition ?? null,
                'shipping' => $product->shipping_info ?? null,
                'specifications' => $product->specifications ?? null,
                'highlights' => $product->highlights ?? null,

                'detail_images' => $product->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->path),
                    'title' => $img->title ?? null,
                ]),

                'video_url' => $product->video_url ?? null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show($slug): JsonResponse
    {
        $product = Product::with([
            'images',
            'inventories',
            'variants.images',
            'variants.inventories',
            'variants.attribute_values.attribute',
            'categories',
            'categories.parent',
            'videos',
            'product_flats',
            'related_products.images', 
        ])->whereHas('product_flats', function ($q) use ($slug) {
            $q->where('url_key', $slug);
        })->firstOrFail();

        if ($product->parent_id) {
            $product = Product::with([
                'images',
                'inventories',
                'variants.images',
                'variants.inventories',
                'variants.attribute_values.attribute',
                'categories',
                'categories.parent',
                'product_flats',
            ])->findOrFail($product->parent_id);
        }

        $selectedFlat = $product->product_flats
            ->firstWhere('url_key', $slug)
            ?? $product->product_flats->first();

        $flat = $this->getFlat($product);

        $minPrice = $product->variants
            ->map(fn ($variant) => $this->getFlat($variant)?->price)
            ->filter()
            ->min();
        /*VISUAL VARIANTS (COLOR / DISPLAY LEVEL)*/

        $visualVariants = collect();

        foreach ($product->variants as $variant) {

            $variantFlat = $this->getFlat($variant);

            // -------------------------------------------------
            // CLEAN TYPE LOGIC (FIXED)
            // -------------------------------------------------
            $hasColor = $variant->attribute_values->contains(
                fn ($a) => $a->attribute?->code === 'color'
            );

            $hasSize = $variant->attribute_values->contains(
                fn ($a) => $a->attribute?->code === 'size'
            );

            if ($hasColor) {
                $type = 'color';
            } elseif ($hasSize) {
                $type = 'size';
            } else {
                $type = 'unknown';
            }

            // -------------------------------------------------
            // SIZE VALUE EXTRACTION (UNCHANGED)
            // -------------------------------------------------
            $sizeValue = $this->getAttributeOptionName($variant, 'size');

            if ($sizeValue) {
                $sizeValue = strtoupper($sizeValue);
            }

            $colorName = $this->getAttributeOptionName($variant, 'color');

            $colorOptionId = $this->getAttributeOptionId($variant, 'color');

            $sizeOptionId = $this->getAttributeOptionId($variant, 'size');

            // -------------------------------------------------
            // PUSH VARIANT (UNCHANGED STRUCTURE)
            // -------------------------------------------------
            $visualVariants->push([
                'id' => $variant->id,
                'name' => $variant->name,

                'hex' => $variant->color_hex ?? null,

                'price' => $variantFlat?->price,

                'stock' => [
                    'qty' => $variant->inventories->sum('qty'),
                    'in_stock' => $variant->inventories->sum('qty') > 0,
                ],

                'type' => $type,

                'color' => [
                    'id' => $colorOptionId,
                    'name' => $colorName,
                    'hex' => $variant->color_hex ?? null,
                ],

                'size' => [
                    'id' => $sizeOptionId,
                    'name' => $sizeValue,
                ],
                'images' => $variant->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->path),
                ]),
                'attributes' => $variant->attribute_values->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->attribute_id,
                        'attribute_code' => $attr->attribute?->code,
                        'attribute_option_id' => $attr->integer_value,
                        'value' => $attr->text_value,
                    ];
                })->values(),
            ]);
        }

        /* SIZE MATRIX (INDEPENDENT STOCK SYSTEM)*/

        $sizeStock = [];

        if ($product->type === 'configurable') {

            foreach ($product->variants as $variant) {

                $size = $this->getAttributeOptionName($variant, 'size');

                if (!$size) {
                    continue;
                }

                $sizeStock[$size] =
                    ($sizeStock[$size] ?? false)
                    || ($variant->inventories->sum('qty') > 0);
            }

        } else {

            $size = $this->getAttributeOptionName($product, 'size');

            if ($size) {
                $sizeStock[$size] = $product->inventories->sum('qty') > 0;
            }
        }
        if ($product->type === 'configurable') {
            $productQty = $product->variants->sum(function ($variant) {
                return $variant->inventories->sum('qty');
            });
        } else {
            $productQty = $product->inventories->sum('qty');
        }
        if ($product->type !== 'configurable') {

            $visualVariants->push([
                'id' => $product->id,
                'name' => $product->name,
                'hex' => null,
                'price' => $flat?->price,
                'stock' => [
                    'qty' => $productQty,
                    'in_stock' => $productQty > 0,
                ],
                'type' => 'simple',
                'color' => null,
                'size' => [
                    'id' => null,
                    'name' => $this->getAttributeOptionName($product, 'size'),
                ],
                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->path),
                ]),
                'attributes' => [],
            ]);
        }

        /*RETURN RESPONSE*/
        return response()->json([
            'data' => [
                'id' => $product->id,
                'type' => $product->type,
                'sku' => $product->sku,
                'slug' => $selectedFlat?->url_key,
                'title' => $product->getAttribute('name'),
                'subtitle' => $product->getAttribute('short_description'),
                'price' => $flat?->price ?? $minPrice,
                'stock' => [
                    'qty' => $productQty,
                    'in_stock' => $productQty > 0,
                ],

                'currency' => 'USD',
                'currency_symbol' => '$',

                'collection' => $product->categories->first()?->name,

                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'parent_id' => $category->parent_id,

                        'parent' => $category->parent ? [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                            'slug' => $category->parent->slug,
                        ] : null,
                    ];
                })->values(),

                'variants' => $visualVariants->map(function ($v) use ($product) {
                    return [
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'hex' => $v['hex'],
                        'stock' => $v['stock'],
                        'price' => $v['price'],
                        'type' => $v['type'],
                        'size' => $v['size'],
                        'color' => $v['color'],
                        'images' => count($v['images']) > 0
                            ? $v['images']
                            : ($product->images->first() ? [[
                                'id' => $product->images->first()->id,
                                'url' => Storage::url($product->images->first()->path),
                            ]] : []),
                        'attributes' => $v['attributes'] ?? [],
                    ];
                })->values(),
                'sizes' => [
                    'options' => $sizeStock,
                    'has_any' => collect($sizeStock)->contains(true),
                ],

                'model_info' => $product->model_info,
                'description' => $product->description,
                'composition' => $product->composition,
                'shipping' => $product->shipping_info,

                'product_details' => ProductDetail::where('product_id', $product->id)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn ($detail) => [
                        'id'           => $detail->id,
                        'title'        => $detail->title,
                        'content'      => $detail->content,
                        'button_label' => $detail->button_label,
                        'button_link'  => $detail->button_link,
                    ])
                    ->values(),

                'size_guide_content' => $product->size_guide_content,

                'size_guide_image' => $product->size_guide_image
                    ? Storage::url($product->size_guide_image)
                    : null,

                'specifications' => [
                    'technical_features' => $this->getTechnicalFeatures($product),
                    'performance' => $this->getPerformance($product),
                ],
                'highlights' => $product->highlights,

                'detail_images' => $product->detail_images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->path),
                    'title' => $img->title ?? null,
                ]),

                'main_image' => $product->images->first() ? [
                    'id' => $product->images->first()->id,
                    'url' => Storage::url($product->images->first()->path),
                ] : null,
                'videos' => $product->videos->map(fn($video) => [
                    'id' => $video->id,
                    'url' => Storage::url($video->path),
                ]),
                'related_products' => $product->related_products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->getAttribute('name'),
                    'slug' => $p->product_flats->first()?->url_key,
                    'price' => $p->product_flats->first()?->price,
                    'image' => $p->images->first()
                        ? Storage::url($p->images->first()->path)
                        : null,
                ];
            })->values(),
            ]
        ]);
    }

    private function getFlat($product)
    {
        return $product->product_flats
            ->where('channel', core()->getRequestedChannelCode())
            ->where('locale', core()->getRequestedLocaleCodeInRequestedChannel())
            ->first();
    }

    private function getAttributeOptionName($variant, string $attributeCode): ?string
    {
        foreach ($variant->attribute_values as $attr) {

            if ($attr->attribute?->code !== $attributeCode) {
                continue;
            }

            if ($attr->text_value) {
                return $attr->text_value;
            }

            if ($attr->integer_value) {
                $option = AttributeOption::find($attr->integer_value);

                return $option?->admin_name;
            }
        }

        return null;
    }

    private function getAttributeOptionId($variant, string $attributeCode): ?int
    {
        foreach ($variant->attribute_values as $attr) {

            if ($attr->attribute?->code === $attributeCode) {
                return $attr->integer_value;
            }
        }

        return null;
    }

    private function getTechnicalFeatures($product)
    {
        $attr = $product->attribute_values
            ->first(fn ($a) => $a->attribute?->code === 'technical_features');

        if (! $attr) {
            return [];
        }

        $value = $attr->text_value ?? $attr->json_value;

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return is_array($value) ? $value : [];
    }

    private function getPerformance($product)
    {
        $attr = $product->attribute_values
            ->first(fn ($a) => $a->attribute?->code === 'performance');

        if (! $attr) {
            return null;
        }

        $value = $attr->text_value ?? $attr->json_value;

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return is_array($value) ? $value : null;
    }
}