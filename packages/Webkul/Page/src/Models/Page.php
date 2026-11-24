<?php

namespace Webkul\Page\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Page\Contracts\Page as PageContract;

use Webkul\Page\Models\PageSection;

class Page extends Model implements PageContract
{
    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'is_home',
        'banner_image',
        'banner_text_position',
        'banner_text_position_mobile',
        'banner_focal_x',
        'banner_focal_y',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_home'   => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getBannerImageUrlAttribute()
    {
        return $this->banner_image
            ? Storage::url($this->banner_image)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }
}