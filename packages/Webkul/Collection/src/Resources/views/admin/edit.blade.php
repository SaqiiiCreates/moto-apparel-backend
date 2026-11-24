<x-admin::layouts>
    <x-slot:title>
        Edit Collection
    </x-slot:title>

    <form action="{{ route('admin.collection.update', $collection->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">
                Edit Collection
            </h1>

            <button type="submit"
                class="px-5 py-2 bg-black hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                Save Changes
            </button>
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-6">

            {{-- Name + Slug --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">
                        Name
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $collection->name) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">
                        Slug (used in Next.js)
                    </label>
                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $collection->slug) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea
                    name="description"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >{{ old('description', $collection->description) }}</textarea>
            </div>

            {{-- Button Text + Link --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Button Text
                    </label>

                    <input
                        type="text"
                        name="button_label"
                        value="{{ old('button_label', $collection->button_label) }}"
                        placeholder="e.g. SHOP MEN"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Button Link
                    </label>

                    <input
                        type="text"
                        name="button_link"
                        value="{{ old('button_link', $collection->button_link) }}"
                        placeholder="e.g. /collections/men"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                </div>
            </div>
            
            {{-- Sort Order --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sort Order
                </label>
                <input
                    type="number"
                    name="sort_order"
                    value="{{ old('sort_order', $collection->sort_order ?? 0) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            {{-- Images Section --}}
            <div class="grid grid-cols-2 gap-6">

                {{-- Thumbnail --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Thumbnail Image
                    </label>

                    @if($collection->thumbnail_image)
                        <img src="{{ asset('storage/' . $collection->thumbnail_image) }}"
                             alt="Thumbnail"
                             class="mb-2 w-32 h-auto border rounded">
                    @endif

                    <p class="text-xs text-gray-500 mb-2">
                        Recommended size: <strong>650 x 900 px</strong><br>
                        Allowed formats: jpg, jpeg, png, webp
                    </p>

                    <input
                        type="file"
                        name="thumbnail_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200"
                    >
                </div>

                {{-- Banner --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Banner Image
                    </label>

                    @if($collection->banner_image)
                        <img src="{{ asset('storage/' . $collection->banner_image) }}"
                             alt="Banner"
                             class="mb-2 w-64 h-auto border rounded">
                    @endif

                    <p class="text-xs text-gray-500 mb-2">
                        Recommended size: <strong>2560 × 1440 px</strong><br>
                        Allowed formats: jpg, jpeg, png, webp
                    </p>

                    <input
                        type="file"
                        name="banner_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200"
                    >
                </div>

            </div>

            {{-- Flags --}}
            <div class="flex gap-8 pt-4 border-t border-gray-200">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('is_active', $collection->is_active) ? 'checked' : '' }}>
                    Active
                </label>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox"
                           name="is_popular"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ old('is_popular', $collection->is_popular) ? 'checked' : '' }}>
                    Mark as Popular
                </label>
            </div>

        </div>
    </form>
    {{-- ============================= --}}
    {{-- Collection Tabs --}}
    {{-- ============================= --}}

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mt-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                Collection Tabs
            </h2>

            <button
                type="button"
                onclick="document.getElementById('new-tab-form').classList.toggle('hidden')"
                class="px-4 py-2 bg-black text-white rounded-lg text-sm hover:bg-gray-800"
            >
                + Add Tab
            </button>
        </div>

        <div
            id="new-tab-form"
            class="hidden rounded-lg border border-gray-200 bg-gray-50 p-5 mb-5"
        >

            <form
                action="{{ route('admin.collection.tabs.store', $collection->id) }}"
                method="POST"
            >
                @csrf

                <div class="grid grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="w-full border rounded-lg px-4 py-2"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Slug
                        </label>

                        <input
                            type="text"
                            name="slug"
                            class="w-full border rounded-lg px-4 py-2"
                            required
                        >
                    </div>

                </div>

                <div class="mt-4">

                    <label class="block text-sm font-medium mb-1">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="3"
                        class="w-full border rounded-lg px-4 py-2"
                    ></textarea>

                </div>

                <div class="grid grid-cols-2 gap-6 mt-4">

                    <div>

                        <label class="block text-sm font-medium mb-1">
                            Sort Order
                        </label>

                        <input
                            type="number"
                            name="sort_order"
                            value="0"
                            class="w-full border rounded-lg px-4 py-2"
                        >

                    </div>

                    <div class="flex items-end">

                        <label class="flex items-center gap-2">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                            >

                            Active

                        </label>

                    </div>

                </div>

                <div class="mt-5 flex justify-end">

                    <button
                        type="submit"
                        class="px-5 py-2 bg-black text-white rounded-lg"
                    >
                        Save Tab
                    </button>

                </div>

            </form>

        </div>

        @if ($collection->tabs->isEmpty())

            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500">
                No tabs have been created for this collection.
            </div>

        @else

            <div class="overflow-hidden rounded-lg border border-gray-200">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Name
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Slug
                            </th>

                            <th class="px-4 py-3 text-center text-sm font-medium">
                                Sort Order
                            </th>

                            <th class="px-4 py-3 text-center text-sm font-medium">
                                Status
                            </th>

                            <th class="px-4 py-3 text-right text-sm font-medium">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($collection->tabs as $tab)

                            <tr class="border-t">

                                <td class="px-4 py-3">
                                    {{ $tab->name }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $tab->slug }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $tab->sort_order }}
                                </td>

                                <td class="px-4 py-3 text-center">

                                    @if($tab->is_active)
                                        <span class="text-green-600">
                                            Active
                                        </span>
                                    @else
                                        <span class="text-red-600">
                                            Inactive
                                        </span>
                                    @endif

                                </td>

                                <td class="px-4 py-3 text-right">

                                    <button
                                        type="button"
                                        onclick="document.getElementById('edit-tab-{{ $tab->id }}').classList.toggle('hidden')"
                                        class="text-blue-600 hover:underline"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        action="{{ route('admin.collection.tabs.delete', [$collection->id, $tab->id]) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Delete this tab?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ml-4 text-red-600 hover:underline"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>
                            <tr
                                id="edit-tab-{{ $tab->id }}"
                                class="hidden bg-gray-50"
                            >
                                <td colspan="5" class="p-5">

                                    <form
                                        action="{{ route('admin.collection.tabs.update', [$collection->id, $tab->id]) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-2 gap-6">

                                            <div>
                                                <label class="block text-sm mb-1">
                                                    Name
                                                </label>

                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ $tab->name }}"
                                                    class="w-full border rounded-lg px-4 py-2"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm mb-1">
                                                    Slug
                                                </label>

                                                <input
                                                    type="text"
                                                    name="slug"
                                                    value="{{ $tab->slug }}"
                                                    class="w-full border rounded-lg px-4 py-2"
                                                    required
                                                >
                                            </div>

                                        </div>

                                        <div class="mt-4">

                                            <label class="block text-sm mb-1">
                                                Description
                                            </label>

                                            <textarea
                                                name="description"
                                                rows="3"
                                                class="w-full border rounded-lg px-4 py-2"
                                            >{{ $tab->description }}</textarea>

                                        </div>
                                        <div>
                                            <label>Linked Categories (products will filter from these)</label>
                                            <select name="categories[]" multiple class="w-full">
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $tab->categories->contains($cat->id) ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-2 gap-6 mt-4">

                                            <div>

                                                <label class="block text-sm mb-1">
                                                    Sort Order
                                                </label>

                                                <input
                                                    type="number"
                                                    name="sort_order"
                                                    value="{{ $tab->sort_order }}"
                                                    class="w-full border rounded-lg px-4 py-2"
                                                >

                                            </div>

                                            <div class="flex items-end">

                                                <label class="flex items-center gap-2">

                                                    <input
                                                        type="checkbox"
                                                        name="is_active"
                                                        value="1"
                                                        {{ $tab->is_active ? 'checked' : '' }}
                                                    >

                                                    Active

                                                </label>

                                            </div>

                                        </div>

                                        <div class="mt-5 flex justify-end">

                                            <button
                                                type="submit"
                                                class="px-5 py-2 bg-black text-white rounded-lg"
                                            >
                                                Update Tab
                                            </button>

                                        </div>

                                    </form>

                                </td>
                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>
</x-admin::layouts>