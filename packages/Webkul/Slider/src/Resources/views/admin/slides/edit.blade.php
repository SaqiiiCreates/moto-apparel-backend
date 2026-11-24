<x-admin::layouts>
    <x-slot:title>Edit Slide</x-slot:title>

    <form action="{{ route('admin.slider.slide.update', [$slider_id, $slide->id]) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input type="hidden" name="focal_x" id="banner_focal_x" value="{{ old('focal_x', $slide->focal_x ?? 50) }}">
        <input type="hidden" name="focal_y" id="banner_focal_y" value="{{ old('focal_y', $slide->focal_y ?? 50) }}">

        <div class="flex gap-[16px] justify-between items-center">
            <p class="text-[20px] text-gray-800 font-bold">Edit Slide</p>
            <div class="flex gap-x-[10px]">
                <a href="{{ route('admin.slider.edit', $slider_id) }}" class="transparent-button">Cancel</a>
                <button type="submit" class="primary-button">Update Slide</button>
            </div>
        </div>

        <div class="flex gap-[10px] mt-5">
            <div class="p-4 bg-white rounded-[4px] box-shadow w-full flex flex-col gap-[20px]">

                <div v-pre>
                    <label class="block text-[12px] text-gray-600 font-medium mb-2">
                        Slide image — click to set focal point
                    </label>

                    <div
                        id="focal-container"
                        onclick="(function (e) {
                            var box = document.getElementById('focal-container');
                            var dot = document.getElementById('focal-dot');
                            var xInput = document.getElementById('banner_focal_x');
                            var yInput = document.getElementById('banner_focal_y');
                            if (!box || !dot || !xInput || !yInput) return;
                            var rect = box.getBoundingClientRect();
                            var x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
                            var y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
                            dot.style.left = x + '%';
                            dot.style.top = y + '%';
                            xInput.value = x.toFixed(2);
                            yInput.value = y.toFixed(2);
                        })(event)"
                        style="position:relative;width:100%;overflow:hidden;cursor:crosshair;border:1px solid #e5e7eb;border-radius:4px;background:#f9fafb;"
                    >
                        <img
                            src="{{ Storage::url($slide->media_path) }}"
                            alt="Slide preview"
                            style="width:100%;height:auto;display:block;pointer-events:none;"
                        >

                        <div
                            id="focal-dot"
                            style="position:absolute;left:{{ $slide->focal_x ?? 50 }}%;top:{{ $slide->focal_y ?? 50 }}%;width:18px;height:18px;background:red;border:3px solid white;border-radius:50%;z-index:9999;transform:translate(-50%,-50%);box-shadow:0 0 0 2px rgba(0,0,0,0.5);pointer-events:none;"
                        ></div>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] text-gray-600 font-medium">Change Slide Image</label>
                    <input type="file" name="media_path" class="w-full text-sm text-gray-500 border rounded-[6px] p-2">
                </div>

                {{-- Heading & Subheading --}}
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Heading</label>
                        <input type="text" name="heading" value="{{ old('heading', $slide->heading) }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium">Sub-Heading</label>
                        <input type="text" name="subheading" value="{{ old('subheading', $slide->subheading) }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                </div>

                {{-- Button Link --}}
                <div>
                    <label class="block text-[12px] text-gray-600 font-medium">Button Link</label>
                    <input type="text" name="button_link" value="{{ old('button_link', $slide->button_link) }}" class="w-full py-2 px-3 border rounded-[6px]">
                </div>

                {{-- Settings --}}
                <div class="flex gap-4">
                    <div class="w-32">
                        <label class="block text-[12px] text-gray-600 font-medium">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order) }}" class="w-full py-2 px-3 border rounded-[6px]">
                    </div>
                    <div class="flex items-center mt-6 gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $slide->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-[12px] text-gray-600 font-medium">Active</label>
                    </div>
                </div>

            </div>
        </div>
    </form>
</x-admin::layouts>