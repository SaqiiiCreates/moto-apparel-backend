@php
    $details = old(
        'product_details',
        \App\Models\ProductDetail::where('product_id', $product->id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($detail) => [
                'title'        => $detail->title,
                'content'      => $detail->content,
                'button_label' => $detail->button_label,
                'button_link'  => $detail->button_link,
            ])
            ->toArray()
    );
@endphp

<div
    id="product-details"
    v-pre
    class="box-shadow mt-3 rounded bg-white p-4 dark:bg-gray-900"
>
    <div class="mb-4 flex items-center justify-between gap-3">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
            Product Details
        </h3>

        <button
            type="button"
            id="add-detail"
            class="secondary-button"
        >
            + Add Section
        </button>
    </div>

    <div id="details-list" class="grid gap-4"></div>

    <div id="detail-template" style="display: none;" v-pre>
    <div class="detail-row rounded border border-gray-200 p-4 dark:border-gray-700">
            <div class="mb-3 flex items-center justify-between gap-2">
                <strong class="detail-number text-sm"></strong>

                <div class="flex gap-2">
                    <button type="button" class="move-up secondary-button" aria-label="Move section up">
                        ↑
                    </button>

                    <button type="button" class="move-down secondary-button" aria-label="Move section down">
                        ↓
                    </button>

                    <button type="button" class="remove-detail secondary-button">
                        Remove
                    </button>
                </div>
            </div>

            <div class="grid gap-3">
                <label class="grid gap-1 text-sm dark:text-white">
                    Section Title

                    <input
                        type="text"
                        data-field="title"
                        class="w-full rounded border border-gray-300 p-2 text-gray-800"
                        required
                    >
                </label>

                <label class="grid gap-1 text-sm dark:text-white">
                    Description

                    <textarea
                        data-field="content"
                        rows="5"
                        class="w-full rounded border border-gray-300 p-2 text-gray-800"
                    ></textarea>
                </label>

                <label class="grid gap-1 text-sm dark:text-white">
                    Button Label (optional)

                    <input
                        type="text"
                        data-field="button_label"
                        class="w-full rounded border border-gray-300 p-2 text-gray-800"
                    >
                </label>

                <label class="grid gap-1 text-sm dark:text-white">
                    Button Link (optional)

                    <input
                        type="text"
                        data-field="button_link"
                        class="w-full rounded border border-gray-300 p-2 text-gray-800"
                        placeholder="/pages/example or https://example.com"
                    >
                </label>
            </div>
        </div>
    </div>

    <input
        type="hidden"
        name="product_details_present"
        value="1"
    >
</div>


@push('scripts')
<script>
(function () {
    const initialDetails = @json($details);

    console.log('PRODUCT DETAILS FROM DATABASE:', initialDetails);

    function initializeProductDetails() {
        const root = document.getElementById('product-details');
        const list = document.getElementById('details-list');
        const template = document.getElementById('detail-template');

        if (!root || !list || !template?.firstElementChild) {
            return;
        }

        if (root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        function renumber() {
            const currentList = document.getElementById('details-list');

            if (!currentList) return;

            const rows = [...currentList.querySelectorAll('.detail-row')];

            rows.forEach((row, index) => {
                row.querySelector('.detail-number').textContent =
                    `Section ${index + 1}`;

                row.querySelectorAll('[data-field]').forEach(input => {
                    input.name =
                        `product_details[${index}][${input.dataset.field}]`;
                });

                row.querySelector('.move-up').disabled = index === 0;

                row.querySelector('.move-down').disabled =
                    index === rows.length - 1;
            });
        }

        function addSection(data = {}) {
            const currentList = document.getElementById('details-list');
            const currentTemplate = document.getElementById('detail-template');

            if (!currentList || !currentTemplate?.firstElementChild) {
                console.error('Product Details template or list missing');
                return;
            }

            const row = currentTemplate.firstElementChild.cloneNode(true);

            const index = currentList.querySelectorAll('.detail-row').length;

            row.querySelectorAll('[data-field]').forEach(input => {
                const field = input.dataset.field;

                input.name = `product_details[${index}][${field}]`;
                input.value = data[field] ?? '';
            });

            currentList.appendChild(row);
            renumber();
        }

        initialDetails.forEach(detail => addSection(detail));

        function restoreSavedValues() {
            const rows = document.querySelectorAll('#details-list .detail-row');

            initialDetails.forEach((detail, index) => {
                const row = rows[index];

                if (!row) return;

                row.querySelectorAll('[data-field]').forEach(input => {
                    const field = input.dataset.field;

                    input.value = detail[field] ?? '';
                });
            });
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(restoreSavedValues);
        });

        document.addEventListener('click', function (event) {
            const addButton = event.target.closest('#add-detail');

            if (addButton) {
                event.preventDefault();
                addSection();
                return;
            }

            const row = event.target.closest('#details-list .detail-row');

            if (!row) return;

            if (event.target.closest('.remove-detail')) {
                event.preventDefault();
                row.remove();
                renumber();
            }

            if (event.target.closest('.move-up')) {
                event.preventDefault();

                const previous = row.previousElementSibling;

                if (previous) {
                    row.parentNode.insertBefore(row, previous);
                    renumber();
                }
            }

            if (event.target.closest('.move-down')) {
                event.preventDefault();

                const next = row.nextElementSibling;

                if (next) {
                    row.parentNode.insertBefore(next, row);
                    renumber();
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeProductDetails);
    } else {
        initializeProductDetails();
    }
})();
</script>
@endpush
