<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationMenuGroup extends Model
{
    protected $fillable = [
        'menu_id',
        'title',
        'link',
        'sort_order',
    ];

    public function menu()
    {
        return $this->belongsTo(NavigationMenu::class, 'menu_id');
    }

    public function items()
    {
        return $this->hasMany(NavigationMenuItem::class, 'group_id');
    }
}