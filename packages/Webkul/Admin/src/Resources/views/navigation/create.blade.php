<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        Create Navigation Menu
    </x-slot>

    <!-- Create Form -->
    <x-admin::form
        :action="route('admin.navigation.store')"
    >
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">

            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Create Navigation Menu
            </p>

            <div class="flex items-center gap-x-2.5">

                <!-- Back Button -->
                <a
                    href="{{ route('admin.navigation.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    Back
                </a>

                <!-- Save Button -->
                <button type="submit" class="primary-button">
                    Save
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                <!-- General Card -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        General
                    </p>

                    <!-- Menu Title -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            Menu Title
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="title"
                            rules="required"
                            placeholder="e.g. Men, Women, Gear Guide"
                        />

                        <x-admin::form.control-group.error control-name="title" />
                    </x-admin::form.control-group>

                    <!-- Menu Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            Menu Location
                        </x-admin::form.control-group.label>

                        @php
                            $menuType = old('menu_type', 'header');
                        @endphp

                        <x-admin::form.control-group.control
                            type="select"
                            name="menu_type"
                            rules="required"
                        >
                            <option value="header" @selected($menuType === 'header')>Header</option>
                            <option value="footer" @selected($menuType === 'footer')>Footer</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="menu_type" />
                    </x-admin::form.control-group>
                </div>

            </div>

            <!-- Right Sidebar -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                <x-admin::accordion>

                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            Settings
                        </p>
                    </x-slot>

                    <x-slot:content>

                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            After creating, you will be redirected to edit page to manage groups & items.
                        </p>

                    </x-slot>

                </x-admin::accordion>

            </div>

        </div>

    </x-admin::form>
</x-admin::layouts>