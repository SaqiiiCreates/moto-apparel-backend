<?php

namespace Webkul\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
        $menu = NavigationMenu::with('groups.items')->findOrFail($id);

        return view('admin::navigation.edit', compact('menu'));
    }

    public function update(Request $request, $id)
    {
        $menu = NavigationMenu::with([
            'groups' => function ($q) {
                $q->orderBy('sort_order');
            },
            'groups.items' => function ($q) {
                $q->orderBy('sort_order');
            }
        ])->findOrFail($id);

        if ($request->has('groups_order')) {

            foreach ($request->groups_order as $groupId => $order) {

                $group = $menu->groups()->find($groupId);

                if ($group) {
                    $group->update([
                        'sort_order' => (int) $order
                    ]);
                }
            }
        }
        /**
         * IMAGE HANDLING
         */
        $imagePath = $menu->image;

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $imagePath = $file->store('navigation', 'public');
        }

        /**
         * 1. Update basic menu info
         */
        $menu->update([
            'title'       => $request->title,
            'sort_order'  => $request->sort_order ?? 0,
            'menu_type' => $request->has('menu_type')
                ? $request->menu_type
                : $menu->menu_type,
            'image_label' => $request->image_label,
            'image_link'  => $request->image_link,
            'image'       => $imagePath,
        ]);

        /**
         * 2. MERGE BOTH EXISTING + NEW GROUPS
         * (IMPORTANT FIX — prevents overwriting old groups)
         */
        $existingGroups = $request->groups ?? [];
        $newGroups      = $request->new_groups ?? [];

        $incomingGroups = array_merge($existingGroups, $newGroups);

        /**
         * 3. Get DB groups
         */
        $dbGroups = $menu->groups()->get()->keyBy('id');

        $processedIds = [];

        /**
         * 4. CREATE / UPDATE GROUPS
         */
        foreach ($incomingGroups as $key => $groupData) {

            $groupTitle = trim($groupData['title'] ?? '');
            $groupLink  = trim($groupData['link'] ?? '');
            $itemsData  = $groupData['items'] ?? [];

            $group = null;

            /**
             * EXISTING GROUP
             */
            if (is_numeric($key) && isset($dbGroups[$key])) {
                $group = $dbGroups[$key];
            }

            /**
             * NEW GROUP
             */
            else {
                $group = new \App\Models\NavigationMenuGroup();
                $group->menu_id = $menu->id;
            }

            if (!$group) {
                continue;
            }

            /**
             * =========================
             * CLEAN ITEMS FIRST
             * =========================
             */
            $cleanItems = [];

            if (!empty($itemsData)) {

                foreach ($itemsData as $itemData) {

                    $itemTitle = trim($itemData['title'] ?? '');
                    $itemLink  = trim($itemData['link'] ?? '');

                    // ❌ skip invalid items
                    if ($itemTitle === '' || $itemLink === '') {
                        continue;
                    }

                    $cleanItems[] = $itemData;
                }
            }

            /**
             * =========================
             * GROUP VALIDATION RULES
             * =========================
             */

            // ❌ if group empty AND no items → delete if exists
            if ($groupTitle === '' && empty($cleanItems) && $group->exists) {

                $group->items()->delete();
                $group->delete();

                continue;
            }

            // ❌ if new group but invalid → skip completely
            if ($groupTitle === '' && empty($cleanItems) && !$group->exists) {
                continue;
            }

            /**
             * SAVE / UPDATE GROUP
             */
            $group->title = $groupTitle !== '' ? $groupTitle : 'Untitled';
            $group->link  = $groupData['link'] ?? null;
            $group->menu_id = $menu->id;
            $group->save();

            $processedIds[] = $group->id;

            /**
             * =========================
             * ITEMS SAVE (ONLY CLEAN ONES)
             * =========================
             */

            $group->items()->delete();

            foreach ($cleanItems as $itemData) {

                $group->items()->create([
                    'title'      => $itemData['title'],
                    'link_type'  => $itemData['type'] ?? 'custom',
                    'custom_url' => $itemData['link'],
                    'sort_order' => $itemData['sort_order'] ?? 0,
                ]);
            }
        }

        /**
         * 6. DELETE REMOVED GROUPS ONLY
         */
        $menu->groups()
            ->whereNotIn('id', $processedIds)
            ->each(function ($group) {
                $group->items()->delete();
                $group->delete();
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