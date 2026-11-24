<?php

namespace Webkul\Collection\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collection\Models\Collection;
use Webkul\Collection\Contracts\CollectionTab as CollectionTabContract;

class CollectionTab extends Model implements CollectionTabContract
{
    protected $table = 'collection_tabs';

    protected $fillable = [
        'collection_id',
        'name',
        'slug',
        'description',
        'banner_image',
        'thumbnail',
        'content',
        'sort_order',
        'is_active',
        'type', 
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // public function products()
    // {
    //     return $this->belongsToMany(
    //         \Webkul\Product\Models\Product::class,
    //         'collection_tab_products',
    //         'collection_tab_id',
    //         'product_id'
    //     )
    //     ->withPivot('sort_order')
    //     ->with(['images']) // optional but useful for frontend
    //     ->orderByPivot('sort_order');
    // }

    public function categories()
    {
        return $this->belongsToMany(
            \Webkul\Category\Models\Category::class,
            'collection_tab_categories',
            'collection_tab_id',
            'category_id'
        );
    }
    
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}