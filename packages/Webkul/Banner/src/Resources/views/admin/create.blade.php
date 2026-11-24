<x-admin::layouts>
    <x-slot:title>Add New Interactive Banner</x-slot:title>

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-4 justify-between items-center">
            <p class="text-xl text-gray-800 font-bold">New Interactive Banner</p>
            <div class="flex gap-x-2">
                <a href="{{ route('admin.banners.index') }}" class="transparent-button">Cancel</a>
                <button type="submit" class="primary-button">Save Banner</button>
            </div>
        </div>

        <div class="flex gap-3 mt-4">
            <div class="flex flex-col gap-4 w-2/3">
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">General Information</p>
                    
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">Internal Title</label>
                            <input type="text" name="title" class="w-full py-2 px-3 border rounded-md" placeholder="Home Page Hero" required>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">Slug (API Identifier)</label>
                            <input type="text" name="slug" class="w-full py-2 px-3 border rounded-md" placeholder="home-hero" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Pre-heading (Subheading)</label>
                        <input type="text" name="preheading" class="w-full py-2 px-3 border rounded-md" placeholder="EFFORTLESS. CASUAL. COMFORTABLE.">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Main Heading</label>
                        <input type="text" name="heading" class="w-full py-2 px-3 border rounded-md" placeholder="The Complete Look">
                    </div>

                    <div class="mb-1">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full py-2 px-3 border rounded-md" placeholder="Nothing Extra. Everything Essential."></textarea>
                    </div>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-2">Interactive Hotspots (JSON)</p>
                    <p class="text-xs text-gray-500 mb-3">Define coordinates (x, y) and product info for the image dots.</p>
                    <textarea name="hotspots" rows="6" class="w-full py-2 px-3 border rounded-md font-mono text-xs bg-gray-50" placeholder='[{"id": 1, "x": "65%", "y": "25%", "label": "Jacket", "desc": "Waterproof jacket", "link": "/jackets"}]'></textarea>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-2">Call to Action Buttons (JSON)</p>
                    <textarea name="buttons" rows="3" class="w-full py-2 px-3 border rounded-md font-mono text-xs bg-gray-50" placeholder='[{"label": "Shop Men", "link": "/men"}, {"label": "Shop Women", "link": "/women"}]'></textarea>
                </div>
            </div>

            <div class="flex flex-col gap-4 w-1/3">
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">Media Assets</p>
                    
                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Desktop Image</label>
                        <input type="file" name="media_path" class="w-full text-xs">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Mobile Image</label>
                        <input type="file" name="mobile_media_path" class="w-full text-xs">
                    </div>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">Settings</p>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-4 h-4">
                        <label for="is_active" class="text-xs text-gray-600 font-medium">Status Active</label>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 font-medium mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full py-2 px-3 border rounded-md">
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>