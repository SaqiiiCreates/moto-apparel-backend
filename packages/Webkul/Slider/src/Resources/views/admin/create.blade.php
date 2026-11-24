<x-admin::layouts>
    <x-slot:title>Add Slider Group</x-slot:title>

    <form action="{{ route('admin.slider.store') }}" method="POST">
        @csrf
        <div class="flex gap-[16px] justify-between items-center">
            <p class="text-[20px] text-gray-800 font-bold">New Slider Group</p>
            <button type="submit" class="primary-button">Save Slider</button>
        </div>

        <div class="flex gap-[10px] mt-[14px]">
            <div class="p-[16px] bg-white rounded-[4px] box-shadow w-full flex flex-col gap-[15px]">
                
                <div class="flex gap-[16px]">
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium required">Title</label>
                        <input type="text" name="title" class="w-full py-2 px-3 border rounded-[6px]" required>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[12px] text-gray-600 font-medium required">Slug (for Next.js API)</label>
                        <input type="text" name="slug" class="w-full py-2 px-3 border rounded-[6px]" required>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] text-gray-600 font-medium">Heading</label>
                    <input type="text" name="heading" class="w-full py-2 px-3 border rounded-[6px]">
                </div>

                <div>
                    <label class="block text-[12px] text-gray-600 font-medium">Description</label>
                    <textarea name="description" class="w-full py-2 px-3 border rounded-[6px]"></textarea>
                </div>

                <div class="flex gap-[20px]">
                    <div class="flex gap-[8px] items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label for="is_active" class="text-[12px] text-gray-600 font-medium">Status Active</label>
                    </div>
                </div>

            </div>
        </div>
    </form>
</x-admin::layouts>