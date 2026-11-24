<x-admin::layouts>

    <x-slot:title>Edit Page: {{ $page->title }}</x-slot:title>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- FORM START --}}
    <form method="POST" action="{{ route('admin.pages.update', $page->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT') 
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hidden Inputs --}}
        <input type="hidden" name="banner_focal_x" id="banner_focal_x" value="{{ $page->banner_focal_x ?? 50 }}">
        <input type="hidden" name="banner_focal_y" id="banner_focal_y" value="{{ $page->banner_focal_y ?? 50 }}">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6 px-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $page->title }}</h1>
                <p class="text-xs text-gray-500">Slug: {{ $page->slug }}</p>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded hover:bg-blue-700 transition">
                    Save Page
                </button>

                <a href="{{ route('admin.pages.index') }}"
                   class="px-4 py-2 bg-gray-100 border rounded text-sm font-semibold hover:bg-gray-200 transition">
                    Back
                </a>
            </div>
        </div>
                
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- LEFT: Controls --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-4 pb-10">

                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

                    <div class="px-5 py-4 border-b bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-700">Page Settings</h2>
                        <p class="text-xs text-gray-400">Edit core details</p>
                    </div>

                    <div class="p-5 space-y-5">

                        {{-- Title --}}
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Title</label>
                            <input type="text"
                                name="title"
                                value="{{ old('title', $page->title) }}"
                                class="w-full mt-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Slug</label>
                            <input type="text"
                                name="slug"
                                value="{{ old('slug', $page->slug) }}"
                                class="w-full mt-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        {{-- Upload --}}
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Banner Image</label>
                            <input type="file"
                                name="banner_image"
                                class="mt-2 w-full text-sm file:px-3 file:py-1.5 file:border-0 file:rounded-md file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                        {{-- Banner Text Position (Desktop) --}}
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">
                                Banner Text Position (Desktop)
                            </label>

                            <select name="banner_text_position"
                                    class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                                <option value="top" {{ $page->banner_text_position == 'top' ? 'selected' : '' }}>Top</option>
                                <option value="middle" {{ $page->banner_text_position == 'middle' ? 'selected' : '' }}>Middle</option>
                                <option value="bottom" {{ $page->banner_text_position == 'bottom' ? 'selected' : '' }}>Bottom</option>
                            </select>
                        </div>

                        {{-- Banner Text Position (Mobile) --}}
                        <div class="mt-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase">
                                Banner Text Position (Mobile)
                            </label>

                            <select name="banner_text_position_mobile"
                                    class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                                <option value="top" {{ $page->banner_text_position_mobile == 'top' ? 'selected' : '' }}>Top</option>
                                <option value="middle" {{ $page->banner_text_position_mobile == 'middle' ? 'selected' : '' }}>Middle</option>
                                <option value="bottom" {{ $page->banner_text_position_mobile == 'bottom' ? 'selected' : '' }}>Bottom</option>
                            </select>
                        </div>
                        {{-- Toggle --}}
                        <div class="flex items-center justify-between border-t pt-4">
                            <span class="text-sm text-gray-600">Published</span>

                            <input type="checkbox"
                                name="is_active"
                                value="1"
                                class="scale-125"
                                {{ $page->is_active ? 'checked' : '' }}>
                        </div>

                        {{-- Meta --}}
                        <div class="text-xs text-gray-400 border-t pt-4">
                            Last updated: {{ $page->updated_at->format('M d, Y') }}
                        </div>

                    </div>
                </div>

                {{-- Save --}}
                <button type="submit"
                        class="w-full py-3 bg-blue-600 text-white text-sm font-bold rounded-xl shadow hover:bg-blue-700 transition">
                    Save Changes
                </button>

            </div>

            {{-- RIGHT: Banner Preview --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-4 pb-10">

                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

                    <div class="px-5 py-4 border-b bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-700">Banner Preview</h2>
                        <p class="text-xs text-gray-400">Live preview of header image</p>
                    </div>

                    <div class="p-5">

                        @if($page->banner_image)
                            <div class="relative group">
                                <!-- Inside the focal-container div -->
                                <div id="focal-container" 
                                    class="relative w-full h-[320px] cursor-crosshair overflow-hidden pointer-events-auto"
                                    style="position: relative; z-index: 20;">
                                    <img src="{{ asset('storage/' . $page->banner_image) }}" 
                                        class="w-full h-full object-cover pointer-events-none">

                                    <!-- Focal Dot - Inline everything -->
                                    <div id="focal-dot" 
                                        style="position: absolute !important; left: 50%; top: 50%; width: 18px; height: 18px; background: red; border: 3px solid white; border-radius: 50%; z-index: 9999; transform: translate(-50%, -50%); box-shadow: 0 0 0 4px black; pointer-events: none;">
                                    </div>
                                </div>

                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-sm transition rounded-lg pointer-events-none">
                                    Current Banner
                                </div>
                            </div>
                        @else
                            <div class="h-[320px] flex items-center justify-center border-2 border-dashed rounded-lg text-gray-400">
                                No banner uploaded
                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-4 pb-10">

            {{-- Sections --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach($page->sections as $section)
                    <div class="bg-white border border-gray-200 mt-5 rounded-lg shadow-sm overflow-hidden">

                        {{-- Section Header --}}
                        <div class="bg-gray-50 px-4 py-2 border-b flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                Section: {{ $section->type }}
                            </span>

                            <button type="button"
                                    onclick="saveSection(this, {{ $page->id }}, {{ $section->id }})"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 uppercase transition">
                                Save Section
                            </button>
                        </div>

                        {{-- Editor --}}
                        <div>
                            <textarea id="huge-{{ $section->id }}"
                                      class="huge-editor-target">
                                {{ $section->content }}
                            </textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </form>
    {{-- FORM END --}}

    {{-- Add Section --}}
    <div class="px-4 pb-10">
        <button type="button" 
                onclick="addNewSection({{ $page->id }})"
                class="w-full py-4 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center gap-2 text-gray-400 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 transition-all group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-xs font-black uppercase tracking-widest">Add New Section</span>
        </button>
    </div>

    {{-- Editor Assets --}}
    @include('page::admin.partials.editor-script')
    @include('page::admin.partials.editor-styles')

    {{-- Auto Slug --}}
    <script>
        document.querySelector('input[name="title"]').addEventListener('input', function() {
            let slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');

            document.querySelector('input[name="slug"]').value = slug;
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("🚀 MY SCRIPT STARTED");

            const container = document.getElementById('focal-container');
            const dot = document.getElementById('focal-dot');
            const xInput = document.getElementById('banner_focal_x');
            const yInput = document.getElementById('banner_focal_y');

            function setDot(x, y) {
                x = Math.max(0, Math.min(100, x));
                y = Math.max(0, Math.min(100, y));

                dot.style.left = x + '%';
                dot.style.top = y + '%';

                xInput.value = x;
                yInput.value = y;

            }

            const rect = container.getBoundingClientRect();
            const initX = parseFloat(xInput.value) || 50;
            const initY = parseFloat(yInput.value) || 50;

            setDot(initX, initY);

            document.addEventListener('pointerdown', function (e) {

                const container = e.target.closest('#focal-container');
                if (!container) return;

                const dot = container.querySelector('#focal-dot');
                const xInput = document.getElementById('banner_focal_x');
                const yInput = document.getElementById('banner_focal_y');

                const rect = container.getBoundingClientRect();

                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                const clampedX = Math.max(0, Math.min(100, x));
                const clampedY = Math.max(0, Math.min(100, y));

                dot.style.left = clampedX + '%';
                dot.style.top = clampedY + '%';

                xInput.value = clampedX;
                yInput.value = clampedY;
            });

        });
    </script>
</x-admin::layouts>
