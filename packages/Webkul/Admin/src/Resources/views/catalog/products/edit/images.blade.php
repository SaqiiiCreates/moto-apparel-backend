{!! view_render_event('bagisto.admin.catalog.product.edit.form.images.before', ['product' => $product]) !!}

<div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">

    <!-- Normal Images -->
    <div class="mb-8">
        <div class="mb-4 flex justify-between gap-5">
            <div class="flex flex-col gap-2">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    Product Images
                </p>

                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                    Upload gallery images
                </p>
            </div>
        </div>

        <x-admin::media.images
            name="images[files]"
            allow-multiple="true"
            show-placeholders="true"
            :uploaded-images="$product->images"
        />

        <x-admin::form.control-group.error control-name='images.files[0]' />
    </div>

    <!-- Detail Images -->
    <div>
        <div class="mb-4 flex justify-between gap-5">
            <div class="flex flex-col gap-2">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    Detail Images
                </p>

                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                    Upload detail/description images
                </p>
            </div>
        </div>

        <x-admin::media.images
            name="detail_images[files]"
            allow-multiple="true"
            show-placeholders="true"
            :uploaded-images="$product->detail_images"
        />

        <x-admin::form.control-group.error control-name='detail_images.files[0]' />
    </div>

</div>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.images.after', ['product' => $product]) !!}