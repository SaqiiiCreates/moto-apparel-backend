<?php

namespace Webkul\Collection\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collection\Contracts\Collection as CollectionContract;

use Webkul\Product\Models\Product;

class Collection extends Model implements CollectionContract
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail_image',
        'banner_image',
        'button_label',
        'button_link',
        'is_popular',
        'is_active',
        'sort_order'
    ];
    
    protected $table = 'collections';

    public function tabs()
    {
        return $this->hasMany(CollectionTab::class)
            ->orderBy('sort_order');
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_product')
            ->withPivot('position')
            ->orderBy('collection_product.position');
    }
}