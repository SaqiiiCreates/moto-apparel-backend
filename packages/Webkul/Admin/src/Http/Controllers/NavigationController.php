<?php

namespace Webkul\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenuGroup;
use App\Models\NavigationMenu;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Webkul\Admin\DataGrids\Navigation\NavigationDataGrid;

class NavigationController extends Controller
{
    public function index()
    {
        $menus = NavigationMenu::latest()->paginate(10);

        return view('admin::navigation.index', compact('menus'));
    }

    public function create()
    {
        return view('admin::navigation.create');
    }

    public function store(Request $request)
    {
        $nextSortOrder = (NavigationMenu::max('sort_order') ?? 0) + 1;

        // check duplicate only within same menu_type
        $exists = NavigationMenu::where('menu_type', $request->menu_type ?? 'header')
            ->where('slug', Str::slug($request->title))
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'title' => 'This title already exists in this section.'
            ]);
        }

        $menu = NavigationMenu::create([
            'title'      => $request->title,
            'slug'       => Str::slug($request->title),
            'sort_order' => $nextSortOrder,
            'menu_type'  => $request->menu_type ?? 'header',
        ]);

        return redirect()->route('admin.navigation.edit', $menu->id);
    }

    public function edit($id)
    {
        $menu = NavigationMenu::with([
            'groups' => fn ($query) => $query->orderBy('sort_order'),
            'groups.items' => fn ($query) => $query->orderBy('sort_order'),
        ])->findOrFail($id);

        return view('admin::navigation.edit', compact('menu'));
    }

    public function update(Request $request, $id)
    {
        $menu = NavigationMenu::findOrFail($id);

        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'menu_type'         => ['required', Rule::in(['header', 'footer'])],
            'bottom_text'       => ['nullable', 'string'],
            'bottom_link_label' => ['nullable', 'string', 'max:255'],
            'bottom_link_url'   => ['nullable', 'string', 'max:255'],
            'image_label'       => ['nullable', 'string', 'max:255'],
            'image_subtitle'    => ['nullable', 'string'],
            'image_link'        => ['nullable', 'string', 'max:255'],
            'image'             => ['nullable', 'image', 'max:5120'],

            'groups'                            => ['nullable', 'array'],
            'groups.*.title'                    => ['nullable', 'string', 'max:255'],
            'groups.*.link'                     => ['nullable', 'string', 'max:255'],
            'groups.*.items'                    => ['nullable', 'array'],
            'groups.*.items.*.title'            => ['nullable', 'string', 'max:255'],
            'groups.*.items.*.type'             => ['nullable', Rule::in(['category', 'collection', 'cms', 'cms_page', 'custom'])],
            'groups.*.items.*.link'             => ['nullable', 'string', 'max:255'],
            'groups.*.items.*.display_style'    => ['nullable', Rule::in(['standard', 'featured'])],

            'groups_order'     => ['nullable', 'array'],
            'items_order'      => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($request, $menu, $validated) {

            // MENU DETAILS
            $imagePath = $menu->image;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('navigation', 'public');
            }

            $menu->update([
                'title'             => $validated['title'],
                'sort_order'        => $validated['sort_order'] ?? 0,
                'menu_type'         => $validated['menu_type'],
                'bottom_text'       => $validated['bottom_text'] ?? null,
                'bottom_link_label' => $validated['bottom_link_label'] ?? null,
                'bottom_link_url'   => $validated['bottom_link_url'] ?? null,
                'image_label'       => $validated['image_label'] ?? null,
                'image_subtitle'    => $validated['image_subtitle'] ?? null,
                'image_link'        => $validated['image_link'] ?? null,
                'image'             => $imagePath,
            ]);

            $incomingGroups = $validated['groups'] ?? [];
            $processedGroupIds = [];

            foreach ($incomingGroups as $groupKey => $groupData) {

                $groupTitle = trim($groupData['title'] ?? '');
                $groupLink = trim($groupData['link'] ?? '');

                $itemsData = $groupData['items'] ?? [];

                // Ignore incomplete items.
                $cleanItems = [];

                foreach ($itemsData as $itemKey => $itemData) {
                    $itemTitle = trim($itemData['title'] ?? '');
                    $itemLink = trim($itemData['link'] ?? '');

                    if ($itemTitle === '' || $itemLink === '') {
                        continue;
                    }

                    $cleanItems[$itemKey] = $itemData;
                }

                if ($groupTitle === '' && empty($cleanItems)) {
                    continue;
                }

                // Find existing group only within this menu.
                $group = is_numeric($groupKey)
                    ? $menu->groups()->find($groupKey)
                    : null;

                if (!$group) {
                    $group = new NavigationMenuGroup();
                    $group->menu_id = $menu->id;
                }

                $group->title = $groupTitle !== '' ? $groupTitle : 'Untitled';
                $group->link = $groupLink !== '' ? $groupLink : null;

                $group->sort_order = (int) (
                    $request->input("groups_order.$groupKey")
                    ?? count($processedGroupIds)
                );

                $group->save();

                $processedGroupIds[] = $group->id;
                $processedItemIds = [];

                // Footer groups don't use navigation items.
                if ($validated['menu_type'] === 'footer') {
                    continue;
                }

                foreach ($cleanItems as $itemKey => $itemData) {

                    // Update existing item instead of recreating it.
                    $item = is_numeric($itemKey)
                        ? $group->items()->find($itemKey)
                        : null;

                    if (!$item) {
                        $item = $group->items()->make();
                    }

                    $item->title = trim($itemData['title']);
                    $item->link_type = match ($itemData['type'] ?? 'custom') {
                        'cms' => 'cms_page',
                        default => $itemData['type'] ?? 'custom',
                    };

                    $item->custom_url = trim($itemData['link']);

                    $item->display_style = $itemData['display_style'] ?? 'standard';

                    $item->sort_order = (int) (
                        $request->input("items_order.$groupKey.$itemKey")
                        ?? count($processedItemIds)
                    );

                    $item->save();

                    $processedItemIds[] = $item->id;
                }

                // Delete only items removed from this group.
                $group->items()
                    ->whereNotIn('id', $processedItemIds)
                    ->delete();
            }

            // Delete groups removed from the menu.
            $menu->groups()
                ->whereNotIn('id', $processedGroupIds)
                ->each(function ($group) {
                    $group->items()->delete();
                    $group->delete();
                });
        });

        return redirect()
            ->route('admin.navigation.edit', $menu->id)
            ->with('success', 'Navigation updated successfully');
    }

    public function destroy($id)
    {
        $menu = NavigationMenu::findOrFail($id);

        // delete related groups + items automatically if FK exists
        $menu->groups()->each(function ($group) {
            $group->items()->delete();
            $group->delete();
        });

        $menu->delete();

        return redirect()
            ->route('admin.navigation.index')
            ->with('success', 'Navigation deleted successfully');
    }
}