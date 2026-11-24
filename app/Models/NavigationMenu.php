<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMenu extends Model
{
    protected $fillable = [
        'title',
        'menu_type',
        'slug',
        'image',
        'image_label',
        'image_link',
        'sort_order',
        'is_active',
    ];

    public function groups()
    {
        return $this->hasMany(NavigationMenuGroup::class, 'menu_id');
    }
}