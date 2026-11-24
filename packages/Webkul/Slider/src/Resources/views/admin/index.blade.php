<x-admin::layouts>
    <x-slot:title>
        Sliders
    </x-slot:title>

    <div class="flex gap-[16px] justify-between items-center max-sm:flex-wrap">
        <p class="text-[20px] text-gray-800 font-bold">
            Sliders
        </p>

        <div class="flex gap-x-[10px] items-center">
            <a href="{{ route('admin.slider.create') }}" class="primary-button">
                Create Slider
            </a>
        </div>
    </div>

    <div class="mt-5">
        <div class="relative overflow-x-auto border rounded-[4px]">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-[14px] text-gray-800 bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 font-medium">ID</th>
                        <th class="px-6 py-3 font-medium">Title</th>
                        <th class="px-6 py-3 font-medium">Slug</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sliders as $slider)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $slider->id }}</td>
                            <td class="px-6 py-4">{{ $slider->title }}</td>
                            <td class="px-6 py-4">{{ $slider->slug }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $slider->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $slider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.slider.edit', $slider->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                    </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($sliders->isEmpty())
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No sliders found. <a href="{{ route('admin.slider.create') }}" class="text-blue-600">Create one?</a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>