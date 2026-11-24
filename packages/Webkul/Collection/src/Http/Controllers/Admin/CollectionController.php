<?php

namespace Webkul\Collection\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Webkul\Collection\Repositories\CollectionRepository;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function __construct(protected CollectionRepository $collectionRepository) {}

    public function index()
    {
        $collections = $this->collectionRepository->all();
        return view('collection::admin.index', compact('collections'));
    }

    public function create()
    {
        return view('collection::admin.create');
    }

    public function store()
    {
        // Validate request
        $data = request()->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|unique:collections,slug|max:255',
            'description'     => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'thumbnail_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner_image'    => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_active'       => 'sometimes|boolean',
            'is_popular'      => 'sometimes|boolean',
        ]);

        // Handle boolean fields
        $data['is_active']  = request()->has('is_active');
        $data['is_popular'] = request()->has('is_popular');

        // Handle file uploads
        if (request()->hasFile('thumbnail_image')) {
            $data['thumbnail_image'] = request()->file('thumbnail_image')
                ->store('collections/thumbnails', 'public');
        }

        if (request()->hasFile('banner_image')) {
            $data['banner_image'] = request()->file('banner_image')
                ->store('collections/banners', 'public');
        }

        // Create collection
        $this->collectionRepository->create($data);

        session()->flash('success', 'Collection created successfully.');
        return redirect()->route('admin.collection.index');
    }

    public function edit($id)
    {
        $collection = $this->collectionRepository->findOrFail($id);
        $categories = app(\Webkul\Category\Repositories\CategoryRepository::class)->getAll(['status' => 1]);
        return view('collection::admin.edit', compact('collection', 'categories'));
    }

    public function update($id)
    {
        $collection = $this->collectionRepository->findOrFail($id);

        $data = request()->validate([
            'name'        => 'nullable|string|max:255',
            'slug'        => [
                'nullable',
                'max:255',
                Rule::unique('collections', 'slug')->ignore($collection->id),
            ],
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'thumbnail_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'banner_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'is_active'   => 'sometimes|boolean',
            'is_popular'  => 'sometimes|boolean',
        ]);        

        $data['name'] = $data['name'] ?? $collection->name;
        $data['slug'] = $data['slug'] ?? $collection->slug;
        
        $data['is_active']  = request()->has('is_active');
        $data['is_popular'] = request()->has('is_popular');        

        // --- Handle Thumbnail ---
        if (request()->hasFile('thumbnail_image')) {
            if ($collection->thumbnail_image) {
                Storage::disk('public')->delete($collection->thumbnail_image);
            }
            $data['thumbnail_image'] = request()->file('thumbnail_image')
                ->store('collections/thumbnails', 'public');
        }

        // --- Handle Banner ---
        if (request()->hasFile('banner_image')) {
            if ($collection->banner_image) {
                Storage::disk('public')->delete($collection->banner_image);
            }
            $data['banner_image'] = request()->file('banner_image')
                ->store('collections/banners', 'public');
        }

        $collection->update($data);

        session()->flash('success', 'Collection updated successfully.');
        return redirect()->route('admin.collection.edit', $id);
    }

    public function destroy($id)
    {
        $collection = $this->collectionRepository->findOrFail($id);

        // Delete images
        Storage::disk('public')->delete([$collection->thumbnail_image, $collection->banner_image]);

        $this->collectionRepository->delete($id);

        session()->flash('success', 'Collection deleted.');
        return redirect()->route('admin.collection.index');
    }
}