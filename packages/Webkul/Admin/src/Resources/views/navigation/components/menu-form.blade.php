<div class="menu-form space-y-4">

    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                Menu Title
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="text"
                name="title"
                :value="$menu->title ?? ''"
                rules="required"
            />
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                Sort Order
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="number"
                name="sort_order"
                :value="$menu->sort_order ?? 0"
            />
        </x-admin::form.control-group>

    </div>

</div>