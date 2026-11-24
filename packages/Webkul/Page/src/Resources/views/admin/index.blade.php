<x-admin::layouts>
    <x-slot:title>
        @lang('Pages')
    </x-slot:title>

    <div class="flex gap-[16px] justify-between items-center max-sm:flex-wrap">
        <p class="text-[20px] text-gray-800 font-bold">
            Pages
        </p>

        <div class="flex gap-x-[10px] items-center">
            <a href="{{ route('admin.pages.create') }}" class="primary-button">
                Create Page
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
                        <th class="px-6 py-3 font-medium">Home</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pages as $page)
                        <tr class="bg-white border-b hover:bg-gray-50">

                            <td class="px-6 py-4">{{ $page->id }}</td>
                            <td class="px-6 py-4">{{ $page->title }}</td>
                            <td class="px-6 py-4">{{ $page->slug }}</td>

                            <td class="px-6 py-4">
                                @if($page->is_home)
                                    <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        Home
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="{{ $page->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $page->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">

                                    <a href="{{ route('admin.pages.edit', $page->id) }}"
                                       class="text-blue-600 hover:underline">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.pages.destroy', $page->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="text-red-600 hover:underline">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @endforeach

                    @if($pages->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                No pages found.
                                <a href="{{ route('admin.pages.create') }}" class="text-blue-600">
                                    Create one?
                                </a>
                            </td>
                        </tr>
                    @endif
                </tbody>

            </table>
        </div>
    </div>
</x-admin::layouts>