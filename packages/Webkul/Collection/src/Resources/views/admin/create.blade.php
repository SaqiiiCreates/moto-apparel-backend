<x-admin::layouts>
    <x-slot:title>
        Add Collection
    </x-slot:title>

    <form action="{{ route('admin.collection.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">
                New Collection
            </h1>

            <button type="submit"
                class="px-5 py-2 bg-black hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                Save Collection
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
                        value="{{ old('name') }}"
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
                        value="{{ old('slug') }}"
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
                >{{ old('description') }}</textarea>
            </div>

            {{-- Sort Order --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sort Order
                </label>
                <input
                    type="number"
                    name="sort_order"
                    value="{{ old('sort_order', 0) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            {{-- Images Section --}}
            <div class="grid grid-cols-2 gap-6">

                {{-- Thumbnail --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">
                        Thumbnail Image
                    </label>

                    <p class="text-xs text-gray-500 mb-2">
                        Recommended size: <strong>650 x 900 px</strong><br>
                        Allowed formats: jpg, jpeg, png, webp
                    </p>

                    <input
                        type="file"
                        name="thumbnail_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200"
                        required
                    >
                </div>

                {{-- Banner --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">
                        Banner Image
                    </label>

                    <p class="text-xs text-gray-500 mb-2">
                        Recommended size: <strong>2560 × 1440 px</strong><br>
                        Allowed formats: jpg, jpeg, png, webp
                    </p>

                    <input
                        type="file"
                        name="banner_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200"
                        required
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
                        {{ old('is_active', true) ? 'checked' : '' }}>
                    Active
                </label>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox"
                        name="is_popular"
                        value="1"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ old('is_popular') ? 'checked' : '' }}>
                    Mark as Popular
                </label>

            </div>

        </div>
    </form>
</x-admin::layouts>