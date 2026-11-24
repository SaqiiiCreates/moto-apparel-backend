<?php

namespace Webkul\Page\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Page\Contracts\PageSection as PageSectionContract;

use Webkul\Page\Models\Page;

class PageSection extends Model implements PageSectionContract
{
    protected $table = 'page_sections';

    protected $fillable = [
        'page_id',
        'type',
        'sort_order',
        'settings',
        'content'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}