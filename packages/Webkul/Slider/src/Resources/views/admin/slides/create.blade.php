<x-admin::layouts>
    <x-slot:title>Add New Slide</x-slot:title>

    <form action="{{ route('admin.slider.slide.store', $slider_id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-[16px] justify-between items-center">
            <p class="text-[20px] text-gray-800 font-bold">Add New Slide</p>
            <div class="flex gap-x-[10px]">
                <a href="{{ route('admin.slider.edit', $slider_id) }}" class="transparent-button">Cancel</a>
                <button type="submit" class="primary-button">Save Slide</button>
            </div>
        </div>

        <div class="flex gap-[10px] mt-5">
            <div class="p-4 bg-white rounded-[4px] box-shadow w-full flex flex-col gap-[20px]">
                
                {{-- Media Path (Desktop Image) --}}
                <div class="flex flex-col gap-2">
                    <label class="block text-[12px] text-gray-600 font-medium required">Slide Image (Desktop)</label>
                    <input type="file" name="media_path" class="w-full text-sm text-gray-500 border rounded-[6px] p-2" required>
                    @error('media_path')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Heading & Subheading --}}
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Heading</label>
                        <input type="text" name="heading" value="{{ old('heading') }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Sub-Heading</label>
                        <input type="text" name="subheading" value="{{ old('subheading') }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                </div>

                {{-- Button Text & Button Link --}}
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Button Text</label>
                        <input type="text" name="button_text" value="{{ old('button_text') }}" placeholder="e.g. Shop Now" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Button Link</label>
                        <input type="text" name="button_link" value="{{ old('button_link') }}" placeholder="https://..." class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                </div>

                <div class="flex gap-4">
                    {{-- Sort Order --}}
                    <div class="w-32">
                        <label class="block text-[12px] text-gray-600 font-medium">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                    {{-- Status --}}
                    <div class="flex items-center mt-6 gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label for="is_active" class="text-[12px] text-gray-600 font-medium">Active</label>
                    </div>
                </div>

            </div>
        </div>
    </form>
</x-admin::layouts>