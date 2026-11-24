<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;

class NavigationApiController extends Controller
{
    public function index()
    {
        $menus = NavigationMenu::with([
            'groups.items'
        ])
        ->orderBy('sort_order')
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'header' => $this->formatMenus($menus, 'header'),
                'footer' => $this->formatMenus($menus, 'footer'),
            ]
        ]);
    }

    private function formatMenus($menus, $type)
    {
        return $menus->where('menu_type', $type)->values()->map(function ($menu) {

            return [
                'id' => $menu->id,
                'title' => $menu->title,
                'sort_order' => $menu->sort_order,

                'image' => $menu->image ? asset('storage/' . $menu->image) : null,
                'image_label' => $menu->image_label,
                'image_link' => $menu->image_link,

                'groups' => $menu->groups->values()->map(function ($group) {

                    return [
                        'id' => $group->id,
                        'title' => $group->title,
                        'link' => $group->link,

                        'items' => $group->items->values()->map(function ($item) {

                            return [
                                'id' => $item->id,
                                'title' => $item->title,
                                'type' => $item->link_type,
                                'link' => $item->custom_url,
                            ];
                        })
                    ];
                })
            ];
        });
    }
}