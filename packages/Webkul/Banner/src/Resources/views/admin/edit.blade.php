<x-admin::layouts>
    <x-slot:title>Edit Interactive Banner: {{ $banner->title }}</x-slot:title>

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="flex gap-4 justify-between items-center">
            <p class="text-xl text-gray-800 font-bold">Edit: {{ $banner->title }}</p>
            <div class="flex gap-x-2">
                <a href="{{ route('admin.banners.index') }}" class="transparent-button">Cancel</a>
                <button type="submit" class="primary-button">Update Banner</button>
            </div>
        </div>

        <div class="flex gap-3 mt-4">
            <div class="flex flex-col gap-4 w-2/3">
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">General Information</p>
                    
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">Internal Title</label>
                            <input type="text" name="title" value="{{ old('title') ?: $banner->title }}" class="w-full py-2 px-3 border rounded-md" required>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 font-medium required mb-1">Slug (API Identifier)</label>
                            <input type="text" name="slug" value="{{ old('slug') ?: $banner->slug }}" class="w-full py-2 px-3 border rounded-md" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Pre-heading (Subheading)</label>
                        <input type="text" name="preheading" value="{{ old('preheading') ?: $banner->preheading }}" class="w-full py-2 px-3 border rounded-md">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Main Heading</label>
                        <textarea
                            name="heading"
                            rows="3"
                            class="w-full py-2 px-3 border rounded-md"
                        >{{ old('heading', $banner->heading) }}
                        </textarea>
                    </div>

                    <div class="mb-1">
                        <label class="block text-xs text-gray-600 font-medium mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full py-2 px-3 border rounded-md">{{ old('description') ?: $banner->description }}</textarea>
                    </div>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-base text-gray-800 font-semibold">Interactive Hotspots (JSON)</p>
                        <span class="text-[10px] bg-gray-100 px-2 py-1 rounded text-gray-500 uppercase tracking-widest font-bold">JSON Format Required</span>
                    </div>
                    <textarea name="hotspots" rows="6" class="w-full py-2 px-3 border rounded-md font-mono text-xs bg-gray-50">@json($banner->hotspots ?? [])</textarea>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-2">Call to Action Buttons (JSON)</p>
                    <textarea name="buttons" rows="3" class="w-full py-2 px-3 border rounded-md font-mono text-xs bg-gray-50">@json($banner->buttons ?? [])</textarea>
                </div>
            </div>

            <div class="flex flex-col gap-4 w-1/3">
                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">Media Assets</p>
                    
                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-2">Desktop Image</label>
                        @if ($banner->media_path)
                            <div class="relative group mb-2 border rounded overflow-hidden">
                                <img src="{{ Storage::url($banner->media_path) }}" class="w-full h-auto object-cover max-h-40">
                            </div>
                        @endif
                        <input type="file" name="media_path" class="w-full text-xs">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-600 font-medium mb-2">Mobile Image</label>
                        @if ($banner->mobile_media_path)
                            <div class="relative group mb-2 border rounded overflow-hidden">
                                <img src="{{ Storage::url($banner->mobile_media_path) }}" class="w-full h-auto object-cover max-h-40">
                            </div>
                        @endif
                        <input type="file" name="mobile_media_path" class="w-full text-xs">
                    </div>
                </div>

                <div class="p-4 bg-white rounded shadow-sm">
                    <p class="text-base text-gray-800 font-semibold mb-4">Settings</p>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }} class="w-4 h-4">
                        <label for="is_active" class="text-xs text-gray-600 font-medium">Status Active</label>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 font-medium mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ $banner->sort_order }}" class="w-full py-2 px-3 border rounded-md">
                    </div>
                </div>

                <div class="p-4 border-2 border-dashed border-gray-200 rounded text-gray-500 text-xs">
                    <p><strong>Next.js Note:</strong> Ensure the Slug matches exactly what is called in your Frontend API route.</p>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>