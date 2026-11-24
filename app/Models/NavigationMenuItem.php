<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMenuItem extends Model
{
    protected $fillable = [
        'title',
        'link_type',
        'custom_url',
        'reference_id',
        'sort_order',
        'group_id',
        'display_style',
    ];

    public function group()
    {
        return $this->belongsTo(NavigationMenuGroup::class, 'group_id');
    }
}