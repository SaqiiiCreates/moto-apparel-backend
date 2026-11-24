<x-admin::layouts>
    <x-slot:title>
        Navigation - {{ $menu->title }}
    </x-slot>

    <x-admin::form
        method="POST"
        :action="route('admin.navigation.update', $menu->id)"
        enctype="multipart/form-data"
    >
        @csrf

        <!-- HEADER -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Edit Navigation - {{ $menu->title }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.navigation.index') }}"
                   class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    Back
                </a>

                <button type="submit" class="primary-button">
                    Save
                </button>
            </div>
        </div>

        <!-- BODY -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

            <!-- LEFT -->
            <div class="flex flex-1 flex-col gap-2">

                <!-- MENU INFO -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Menu Details
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Title</x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="title"
                            :value="$menu->title"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Sort Order</x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="number"
                            name="sort_order"
                            :value="$menu->sort_order"
                        />
                    </x-admin::form.control-group>

                    @foreach ([
                        'bottom_text' => 'Bottom Text',
                        'bottom_link_label' => 'Bottom Link Label',
                        'bottom_link_url' => 'Bottom Link URL',
                    ] as $field => $label)

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                {{ $label }}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                :name="$field"
                                :value="old($field, $menu->{$field})"
                            />
                        </x-admin::form.control-group>

                    @endforeach

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            Menu Location
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="menu_type"
                            id="menu-type-select"
                            rules="required"
                            :value="$menu->menu_type"
                        >
                            <option value="header" @selected(($menu->menu_type ?? 'header') === 'header')>Header</option>
                            <option value="footer" @selected(($menu->menu_type ?? '') === 'footer')>Footer</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="menu_type" />
                    </x-admin::form.control-group>
                </div>

                <!-- GROUPS -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

                    <div class="flex justify-between items-center mb-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            Groups (Drag & Drop)
                        </p>

                        <button type="button"
                                class="primary-button text-sm"
                                onclick="addGroup()">
                            + Add Group
                        </button>
                    </div>

                    <div id="groups-container">

                        @foreach($menu->groups as $group)

                            <div class="group-box border p-3 mb-3 rounded bg-white dark:bg-gray-800"
                                 data-group-id="{{ $group->id }}">

                                <!-- DRAG HANDLE -->
                                <div class="group-header flex items-center gap-2 mb-2">

                                    <span class="group-drag-handle cursor-move text-gray-500">
                                        ☰
                                    </span>

                                    <input
                                        type="text"
                                        name="groups[{{ $group->id }}][title]"
                                        value="{{ $group->title }}"
                                        class="border p-1 w-60"
                                    >
                                    <input
                                        type="text"
                                        name="groups[{{ $group->id }}][link]"
                                        value="{{ $group->link }}"
                                        class="border p-1 w-60"
                                        placeholder="Group Link"
                                    />
                                    <button type="button"
                                            class="text-red-500 ml-auto"
                                            onclick="this.closest('.group-box').remove()">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>

                                </div>

                                <!-- ITEMS (only for header) -->
                                <div class="items-section">
                                    <div class="items-container ml-6 space-y-2">

                                        @foreach($group->items as $item)

                                            <div class="item border p-2 rounded bg-gray-50 dark:bg-gray-700"
                                                 data-item-id="{{ $item->id }}">

                                                <div class="flex items-center gap-2 mb-2">

                                                    <span class="cursor-move-item text-gray-500">
                                                        ⇅
                                                    </span>

                                                    <input
                                                        type="text"
                                                        name="groups[{{ $group->id }}][items][{{ $item->id }}][title]"
                                                        value="{{ $item->title }}"
                                                        class="border p-1 flex-1"
                                                    >

                                                    <select
                                                        name="groups[{{ $group->id }}][items][{{ $item->id }}][display_style]"
                                                        class="border p-1 w-36"
                                                    >
                                                        <option value="standard"
                                                            @selected(($item->display_style ?? 'standard') === 'standard')>
                                                            Standard
                                                        </option>

                                                        <option value="featured"
                                                            @selected($item->display_style === 'featured')>
                                                            Featured
                                                        </option>
                                                    </select>

                                                    <button type="button"
                                                            class="text-red-500"
                                                            onclick="this.closest('.item').remove()">
                                                        <i class="ri-close-line"></i>
                                                    </button>

                                                </div>

                                                <select
                                                    name="groups[{{ $group->id }}][items][{{ $item->id }}][type]"
                                                    class="border p-1 w-full mb-2"
                                                >
                                                    <option value="category" @selected($item->link_type == 'category')>Category</option>
                                                    <option value="collection" @selected($item->link_type == 'collection')>Collection</option>
                                                    <option value="cms" @selected($item->link_type == 'cms_page')>CMS Page</option>
                                                    <option value="custom" @selected($item->link_type == 'custom')>Custom URL</option>
                                                </select>

                                                <input
                                                    type="text"
                                                    name="groups[{{ $group->id }}][items][{{ $item->id }}][link]"
                                                    value="{{ $item->custom_url }}"
                                                    class="border p-1 w-full"
                                                    placeholder="Link / ID"
                                                >

                                            </div>

                                        @endforeach

                                    </div>

                                    <!-- ADD ITEM -->
                                    <button type="button"
                                            class="text-sm text-blue-600 mt-2 add-item-btn"
                                            onclick="addItem(this)">
                                        + Add Item
                                    </button>
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="w-[360px] max-w-full">

                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Media
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Menu Image</x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="file" name="image"/>
                        @if($menu->image)
                            <div class="mt-3">
                                <img
                                    src="{{ asset('storage/' . $menu->image) }}"
                                    alt="Menu Image"
                                    class="w-40 rounded border"
                                >
                            </div>
                        @endif
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            Image Subtitle
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="image_subtitle"
                            :value="old('image_subtitle', $menu->image_subtitle)"
                        />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Image Label</x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="image_label"
                            :value="$menu->image_label"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>Image Link</x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="image_link"
                            :value="$menu->image_link"
                        />
                    </x-admin::form.control-group>

                </div>

            </div>

        </div>
    </x-admin::form>

    <!-- SORTABLE JS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Toggle items visibility based on menu type
            const menuTypeSelect = document.getElementById('menu-type-select');

            function toggleItemsVisibility() {
                const isHeader = menuTypeSelect.value === 'header';
                document.querySelectorAll('.items-section').forEach(el => {
                    el.style.display = isHeader ? 'block' : 'none';
                });
            }

            // Initial check
            toggleItemsVisibility();

            // On change
            menuTypeSelect.addEventListener('change', toggleItemsVisibility);

            // Sortable for groups
            new Sortable(document.getElementById('groups-container'), {
                animation: 150,
                handle: '.group-drag-handle',
                draggable: '.group-box',
                ghostClass: 'sortable-ghost',
                filter: 'input, textarea, select, button',
                preventOnFilter: false,
                forceFallback: true,
                onEnd: function () {
                    saveGroupOrder();
                }
            });

            // Sortable for items
            document.querySelectorAll('.items-container').forEach((el) => {
                new Sortable(el, {
                    animation: 150,
                    handle: '.cursor-move-item',
                    ghostClass: 'sortable-ghost',
                    draggable: '.item',
                    onEnd: function () {
                        saveItemOrder();
                    }
                });
            });
        });

        function addGroup() {
            let container = document.getElementById('groups-container');
            let id = Date.now();
            const isHeader = document.getElementById('menu-type-select').value === 'header';

            let html = `
                <div class="group-box border p-3 mb-3 rounded bg-white dark:bg-gray-800"
                    data-group-id="new_${id}">

                    <div class="group-header flex items-center gap-2 mb-2">
                        <span class="group-drag-handle cursor-move text-gray-500">☰</span>

                        <input type="text"
                            name="groups[new_${id}][title]"
                            class="border p-1 w-60"
                            placeholder="Group Title">

                        <input type="text"
                            name="groups[new_${id}][link]"
                            class="border p-1 w-60"
                            placeholder="Group Link">

                        <button type="button"
                                class="text-red-500 ml-auto"
                                onclick="this.closest('.group-box').remove()"
                                title="Remove Group">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>

                    <div class="items-section" style="display: ${isHeader ? 'block' : 'none'}">
                        <div class="items-container ml-6 space-y-2"></div>

                        <button type="button"
                                class="text-sm text-blue-600 mt-2 add-item-btn"
                                onclick="addItem(this)">
                            + Add Item
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);

            // Re-initialize sortable for the new items container
            const newItemsContainer = container.lastElementChild.querySelector('.items-container');
            if (newItemsContainer) {
                new Sortable(newItemsContainer, {
                    animation: 150,
                    handle: '.cursor-move-item',
                    ghostClass: 'sortable-ghost',
                    draggable: '.item',
                    onEnd: function () {
                        saveItemOrder();
                    }
                });
            }
        }

        function addItem(btn) {
            // Extra safety: don't allow adding items if footer is selected
            if (document.getElementById('menu-type-select').value !== 'header') {
                return;
            }

            let groupBox = btn.closest('.group-box');
            let groupId = groupBox.dataset.groupId || 'new_' + Date.now();
            let container = groupBox.querySelector('.items-container');
            let id = Date.now();

            let html = `
                <div class="item border p-2 rounded bg-gray-50 dark:bg-gray-700"
                    data-item-id="${id}">

                    <div class="flex items-center gap-2 mb-2">
                        <span class="cursor-move-item text-gray-500">⇅</span>

                        <input type="text"
                            name="groups[${groupId}][items][${id}][title]"
                            class="border p-1 flex-1"
                            placeholder="Item Title">

                        <select
                            name="groups[${groupId}][items][${id}][display_style]"
                            class="border p-1 w-36"
                        >
                            <option value="standard">Standard</option>
                            <option value="featured">Featured</option>
                        </select>

                        <button type="button"
                                class="text-red-500 font-bold text-lg"
                                onclick="this.closest('.item').remove()"
                                title="Remove Item">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>

                    <select name="groups[${groupId}][items][${id}][type]" class="border p-1 w-full mb-2">
                        <option value="category">Category</option>
                        <option value="collection">Collection</option>
                        <option value="cms">CMS Page</option>
                        <option value="custom">Custom URL</option>
                    </select>

                    <input type="text"
                        name="groups[${groupId}][items][${id}][link]"
                        class="border p-1 w-full"
                        placeholder="Link / ID">
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }

        document.querySelector("form").addEventListener("submit", function (e) {
            saveGroupOrder();
            saveItemOrder();
        });

        function saveGroupOrder() {
            document.querySelectorAll('input[name^="groups_order"]').forEach(el => el.remove());

            document.querySelectorAll("#groups-container .group-box").forEach((group, index) => {
                let groupId = group.dataset.groupId;
                if (!groupId) return;

                let input = document.createElement("input");
                input.type = "hidden";
                input.name = `groups_order[${groupId}]`;
                input.value = index;

                group.appendChild(input);
            });
        }

        function saveItemOrder() {
            document.querySelectorAll('input[name^="items_order"]').forEach(el => el.remove());

            document.querySelectorAll(".group-box").forEach((group) => {
                let groupId = group.dataset.groupId;

                group.querySelectorAll(".item").forEach((item, index) => {
                    let itemId = item.dataset.itemId;
                    if (!itemId) return;

                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = `items_order[${groupId}][${itemId}]`;
                    input.value = index;

                    item.appendChild(input);
                });
            });
        }
    </script>

    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: #e5e7eb;
        }

        .group-box input,
        .group-box select,
        .group-box button {
            pointer-events: auto;
        }

        .group-drag-handle {
            cursor: grab;
        }

        .group-drag-handle:active {
            cursor: grabbing;
        }
    </style>

</x-admin::layouts>