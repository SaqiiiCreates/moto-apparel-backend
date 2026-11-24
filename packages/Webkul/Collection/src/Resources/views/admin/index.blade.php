<x-admin::layouts>
    <x-slot:title>
        Collections
    </x-slot:title>

    <div class="flex gap-[16px] justify-between items-center max-sm:flex-wrap">
        <p class="text-[20px] text-gray-800 font-bold">
            Collections
        </p>

        <div class="flex gap-x-[10px] items-center">
            <a href="{{ route('admin.collection.create') }}" class="primary-button">
                Create Collection
            </a>
        </div>
    </div>

    <div class="mt-5">
        <div class="relative overflow-x-auto border rounded-[4px]">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-[14px] text-gray-800 bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 font-medium">ID</th>
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Slug</th>
                        <th class="px-6 py-3 font-medium">Popular</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($collections as $collection)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $collection->id }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $collection->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $collection->slug }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="{{ $collection->is_popular ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $collection->is_popular ? 'Yes' : 'No' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="{{ $collection->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $collection->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.collection.edit', $collection->id) }}"
                                       class="text-blue-600 hover:underline">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.collection.delete', $collection->id) }}"
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
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                No collections found.
                                <a href="{{ route('admin.collection.create') }}"
                                   class="text-blue-600 hover:underline">
                                    Create one?
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>