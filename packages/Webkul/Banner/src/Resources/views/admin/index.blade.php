<x-admin::layouts>
    <x-slot:title>
        @lang('Banners')
    </x-slot:title>

    <div class="flex gap-[16px] justify-between items-center max-sm:flex-wrap">
        <p class="text-[20px] text-gray-800 font-bold">
            Banners
        </p>

        <div class="flex gap-x-[10px] items-center">
            <a href="{{ route('admin.banners.create') }}" class="primary-button">
                Create Banner
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
                    @foreach ($banners as $banner)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $banner->id }}</td>
                            <td class="px-6 py-4">{{ $banner->title }}</td>
                            <td class="px-6 py-4">{{ $banner->slug }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                    
                                    <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($banners->isEmpty())
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No banners found. <a href="{{ route('admin.banners.create') }}" class="text-blue-600">Create one?</a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>