<x-admin::layouts>
    <x-slot:title>Create New Page</x-slot:title>

    <form action="{{ route('admin.pages.store') }}" method="POST">
        @csrf

        <div class="flex gap-4 justify-between items-center">
            <p class="text-xl text-gray-800 font-bold">New Page</p>

            <div class="flex gap-x-2">
                <a href="{{ route('admin.pages.index') }}" class="transparent-button">
                    Cancel
                </a>

                <button type="submit" class="primary-button">
                    Save Page
                </button>
            </div>
        </div>

        <div class="flex gap-3 mt-4">

            <!-- LEFT SIDE -->
            <div class="flex flex-col gap-4 w-2/3">

                <!-- GENERAL INFO -->
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">
                        General Information
                    </p>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">
                                Page Title
                            </label>
                            <input type="text"
                                   name="title"
                                   class="w-full py-2 px-3 border rounded-md"
                                   placeholder="Home Page"
                                   required>
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">
                                Slug
                            </label>
                            <input type="text"
                                   name="slug"
                                   class="w-full py-2 px-3 border rounded-md"
                                   placeholder="home"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- INFO NOTE (IMPORTANT CHANGE) -->
                <div class="p-4 bg-blue-50 border border-blue-100 rounded">
                    <p class="text-sm text-blue-700 font-medium">
                        This page will not contain content here.
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        Sections and blocks will be added after creation in the page builder.
                    </p>
                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="flex flex-col gap-4 w-1/3">

                <!-- SETTINGS -->
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">
                        Settings
                    </p>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               checked
                               class="w-4 h-4">

                        <label for="is_active" class="text-xs text-gray-600 font-medium">
                            Active
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox"
                               name="is_home"
                               id="is_home"
                               value="1"
                               class="w-4 h-4">

                        <label for="is_home" class="text-xs text-gray-600 font-medium">
                            Set as Homepage
                        </label>
                    </div>
                </div>

            </div>

        </div>
    </form>
</x-admin::layouts>