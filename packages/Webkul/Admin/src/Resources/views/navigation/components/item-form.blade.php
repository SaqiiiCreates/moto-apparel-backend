@php
    $itemId = $item->id ?? 'new_' . rand(1000,9999);
@endphp

<div class="item border p-2 rounded bg-gray-50 dark:bg-gray-700"
     data-item-id="{{ $itemId }}">

    <!-- DRAG HANDLE -->
    <div class="cursor-move-item font-medium mb-2 text-gray-600 dark:text-gray-200">
        ⇅ Item
    </div>

    <!-- TITLE -->
    <input
        type="text"
        name="items[{{ $itemId }}][title]"
        value="{{ $item->title ?? '' }}"
        class="border p-1 w-full mb-2"
        placeholder="Item Title"
    >

    <!-- TYPE -->
    <select
        name="items[{{ $itemId }}][type]"
        class="border p-1 w-full mb-2"
    >
        <option value="category" @selected(($item->type ?? '') == 'category')>Category</option>
        <option value="collection" @selected(($item->type ?? '') == 'collection')>Collection</option>
        <option value="cms" @selected(($item->type ?? '') == 'cms')>CMS Page</option>
        <option value="custom" @selected(($item->type ?? '') == 'custom')>Custom URL</option>
    </select>

    <!-- LINK -->
    <input
        type="text"
        name="items[{{ $itemId }}][link]"
        value="{{ $item->link ?? '' }}"
        class="border p-1 w-full"
        placeholder="Link / ID / URL"
    >

</div>