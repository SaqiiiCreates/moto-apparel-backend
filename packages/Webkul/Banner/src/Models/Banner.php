<?php

namespace Webkul\Banner\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Banner\Contracts\Banner as BannerContract;

class Banner extends Model implements BannerContract
{
    protected $fillable = [        
        'title',
        'slug',
        'media_path',
        'mobile_media_path',
        'preheading',
        'heading',
        'description',
        'buttons',
        'hotspots',        
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'buttons'  => 'array',
        'hotspots' => 'array',
    ];

    protected $table = 'banner';
}