<div class="group-box border p-3 mb-3 rounded bg-white dark:bg-gray-800"
     data-group-id="{{ $group->id ?? 'new_' . rand(1000,9999) }}">

    <!-- HEADER -->
    <div class="group-header cursor-move font-semibold mb-2">
        ☰ Group

        <input
            type="text"
            name="groups[{{ $group->id ?? 'new_' . rand(1000,9999) }}][title]"
            value="{{ $group->title ?? '' }}"
            class="border p-1 ml-2 w-60"
            placeholder="Group Title"
        >
    </div>

    <!-- ITEMS CONTAINER -->
    <div class="items-container ml-4 space-y-2">

        @if(!empty($group->items))
            @foreach($group->items as $item)
                @include('admin::navigation.components.itemForm', ['item' => $item])
            @endforeach
        @endif

    </div>

    <!-- ADD ITEM BUTTON -->
    <button
        type="button"
        class="mt-2 text-sm text-blue-600"
    >
        + Add Item
    </button>

</div>