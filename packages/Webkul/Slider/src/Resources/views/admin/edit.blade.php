<x-admin::layouts>
    <x-slot:title>Edit Slider Group</x-slot:title>

    <form action="{{ route('admin.slider.update', $slider->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="flex gap-[16px] justify-between items-center">
            <p class="text-[20px] text-gray-800 font-bold">Edit: {{ $slider->title }}</p>
            <div class="flex gap-x-[10px]">
                <a href="{{ route('admin.slider.index') }}" class="transparent-button">Cancel</a>
                <button type="submit" class="primary-button">Update Slider Group</button>
            </div>
        </div>

        {{-- Slider Group Details Card --}}
        <div class="flex gap-[10px] mt-5">
            <div class="p-4 bg-white rounded-[4px] box-shadow w-full flex flex-col gap-[15px]">
                <div class="flex gap-[16px]">
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium required">Title</label>
                        <input type="text" name="title" value="{{ old('title') ?: $slider->title }}" class="w-full py-2 px-3 border rounded-[6px]" required>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium required">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') ?: $slider->slug }}" class="w-full py-2 px-3 border rounded-[6px]" required>
                    </div>
                </div>

                <div class="flex gap-[20px]">
                    <div class="flex gap-[8px] items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $slider->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="text-[12px] text-gray-600 font-medium">Status Active</label>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Slides Management Section --}}
    <div class="p-4 bg-white rounded-[4px] box-shadow w-full flex flex-col gap-[15px] mt-5">
        <div class="flex justify-between items-center border-b pb-3">
            <p class="text-[16px] text-gray-800 font-semibold">Manage Slides</p>
            <a href="{{ route('admin.slider.slide.create', $slider->id) }}" class="secondary-button">
                + Add New Slide
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 mt-2">
            @forelse ($slider->slides->sortBy('sort_order') as $slide)
                <div class="flex gap-4 p-3 border rounded-[6px] items-center hover:bg-gray-50 transition-all">
                    {{-- Slide Thumbnail --}}
                    <div class="w-24 h-16 rounded border overflow-hidden bg-gray-100 flex-shrink-0">
                        <img src="{{ Storage::url($slide->media_path) }}" class="w-full h-full object-cover">
                    </div>
                    
                    {{-- Slide Info --}}
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800">{{ $slide->heading ?: 'No Heading' }}</p>
                        <p class="text-xs text-gray-500 truncate max-w-xs">{{ $slide->button_link ?: 'No Link' }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-4">
                        <span class="text-[11px] px-2 py-1 rounded {{ $slide->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $slide->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        
                        <a href="{{ route('admin.slider.slide.edit', [$slider->id, $slide->id]) }}" class="text-blue-600 text-sm font-medium hover:underline">
                            Edit
                        </a>

                        <form action="{{ route('admin.slider.slide.delete', [$slider->id, $slide->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slide?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center border-2 border-dashed rounded-lg">
                    <p class="text-gray-400">No slides added yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin::layouts>