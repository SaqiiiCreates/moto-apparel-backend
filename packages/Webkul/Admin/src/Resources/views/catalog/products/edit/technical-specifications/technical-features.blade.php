@php
    $value = old('technical_features');

    if (! $value) {
        $raw = $product->technical_features ?? [];

        $value = is_string($raw)
            ? json_decode($raw, true)
            : $raw;
    }

    $value = $value ?: [];
@endphp

<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900 mt-4">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-base font-semibold">Technical Features</h2>

        <button type="button" onclick="addFeatureRow()" class="primary-button">
            + Add Feature
        </button>
    </div>

    <div id="features-wrapper">

        @if(!empty($value))
            @foreach($value as $featureKey => $feature)
                @php $i = $loop->index; @endphp

                <div class="flex gap-2 mb-2 feature-row">

                    {{-- Feature Name --}}
                    <input type="text"
                        name="technical_features_keys[{{ $i }}]"
                        value="{{ $featureKey }}"
                        placeholder="Feature Name (e.g Waterproof)"
                        class="form-control"
                    />

                    {{-- Description --}}
                    <input type="text"
                        name="technical_features_values[{{ $i }}][description]"
                        value="{{ $feature['description'] ?? '' }}"
                        placeholder="Description"
                        class="form-control"
                    />

                    {{-- Icon --}}
                    <input type="text"
                        name="technical_features_values[{{ $i }}][icon]"
                        value="{{ $feature['icon'] ?? '' }}"
                        placeholder="Icon (e.g droplets)"
                        class="form-control"
                    />

                    <button type="button" onclick="this.parentElement.remove()">
                        Remove
                    </button>

                </div>
            @endforeach
        @endif

    </div>
</div>

<script>
let index = document.querySelectorAll('.feature-row').length;

function addFeatureRow() {
    const wrapper = document.getElementById('features-wrapper');

    const row = document.createElement('div');
    row.classList.add('flex', 'gap-2', 'mb-2', 'feature-row');

    row.innerHTML = `
        <input
            type="text"
            name="technical_features_keys[${index}]"
            placeholder="Feature Name"
            class="form-control"
        />

        <input
            type="text"
            name="technical_features_values[${index}][description]"
            placeholder="Description"
            class="form-control"
        />

        <input
            type="text"
            name="technical_features_values[${index}][icon]"
            placeholder="Icon"
            class="form-control"
        />

        <button type="button" onclick="this.parentElement.remove()">
            Remove
        </button>
    `;

    wrapper.appendChild(row);
    index++;
}
</script>