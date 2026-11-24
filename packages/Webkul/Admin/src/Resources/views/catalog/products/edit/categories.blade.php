{!! view_render_event('bagisto.admin.catalog.product.edit.form.categories.before', ['product' => $product]) !!}
<script>
    console.log("categories.blade.php loaded");
</script>
<!-- Panel -->
<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <!-- Panel Header -->
    <p class="mb-4 flex justify-between text-base font-semibold text-gray-800 dark:text-white">
        @lang('admin::app.catalog.products.edit.categories.title')
    </p>

    {!! view_render_event('bagisto.admin.catalog.product.edit.form.categories.controls.before', ['product' => $product]) !!}

    <!-- Panel Content -->
    <div class="mb-5 text-sm text-gray-600 dark:text-gray-300">

        <v-product-categories>
            <x-admin::shimmer.tree />
        </v-product-categories>

    </div>

    {!! view_render_event('bagisto.admin.catalog.product.edit.form.categories.controls.after', ['product' => $product]) !!}
</div>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.categories.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-categories-template"
    >
        <div>
            <template v-if="isLoading">
                <x-admin::shimmer.tree />
            </template>

            <template v-else>
                <x-admin::tree.view
                    input-type="checkbox"
                    selection-type="individual"
                    name-field="categories"
                    id-field="id"
                    value-field="id"
                    ::items="categories"
                    :value="json_encode($product->categories->pluck('id'))"
                    :fallback-locale="config('app.fallback_locale')"
                />
            </template>
        </div>
    </script>

    <script type="module">
    console.log("Categories JS loaded");

        app.component('v-product-categories', {
            template: '#v-product-categories-template',

            data() {
                console.log("data() called");

                return {
                    isLoading: true,
                    categories: [],
                };
            },

            mounted() {
                console.log("v-product-categories mounted");
                this.get();
            },

            methods: {
                get() {
                    console.log("get() called");

                    const url = "{{ route('admin.catalog.categories.tree') }}";

                    console.log("URL:", url);

                    axios.get(url, {
                        params: {
                            channel: "{{ $currentChannel->code }}",
                        }
                    })
                    .then(response => {
                        console.log("SUCCESS", response);

                        this.isLoading = false;
                        this.categories = response.data.data;
                    })
                    .catch(error => {
                        console.log("FAILED");
                        console.log(error);
                        console.log(error.response);
                    });
                }
            }
        });
    </script>
@endpushOnce
