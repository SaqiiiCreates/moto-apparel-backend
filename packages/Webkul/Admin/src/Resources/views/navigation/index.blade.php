<x-admin::layouts>
    <x-slot:title>
        Navigation
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Navigation
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.navigation.create') }}"
                class="primary-button"
            >
                + Create Menu
            </a>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="px-6 py-4 font-semibold">ID</th>
                    <th class="px-6 py-4 font-semibold">Menu Title</th>
                    <th class="px-6 py-4 font-semibold">Location</th>
                    <th class="px-6 py-4 font-semibold">Created At</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($menus as $menu)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-6 py-4">
                            {{ $menu->id }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $menu->title }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded 
                                {{ $menu->menu_type === 'header' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($menu->menu_type) }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4">
                            {{ $menu->created_at?->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a
                                    href="{{ route('admin.navigation.edit', $menu->id) }}"
                                    class="text-blue-600 hover:underline"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.navigation.destroy', $menu->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this menu?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-red-600 hover:underline"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="5"
                            class="px-6 py-10 text-center text-gray-500"
                        >
                            No menus found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $menus->links() }}
    </div>
</x-admin::layouts>