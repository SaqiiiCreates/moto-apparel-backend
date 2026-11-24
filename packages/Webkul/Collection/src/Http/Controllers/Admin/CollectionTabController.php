<?php

namespace Webkul\Collection\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Webkul\Collection\Repositories\CollectionRepository;
use Webkul\Collection\Repositories\CollectionTabRepository;
use Webkul\Collection\Models\Collection;
use Illuminate\Support\Facades\Log;

class CollectionTabController extends Controller
{
    public function __construct(
        protected CollectionRepository $collectionRepository,
        protected CollectionTabRepository $collectionTabRepository
    ) {}

    
    public function store(Collection $collection)
    {
        // Log::info('CollectionTabController@store - START', [
        //     'collection_id' => $collection->id,
        //     'request' => request()->all(),
        // ]);

        $data = request()->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => [
                'required',
                'max:255',
                Rule::unique('collection_tabs', 'slug')
                    ->where('collection_id', $collection->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        // Log::info('Validation passed', $data);

        $data['collection_id'] = $collection->id;
        $data['is_active'] = request()->has('is_active');

        // Log::info('Before repository create', $data);

        $tab = $this->collectionTabRepository->create($data);

        if (request()->has('categories')) {
            $tab->categories()->sync(request('categories'));
        }

        // Log::info('After repository create', [
        //     'tab_id' => $tab->id,
        // ]);

        session()->flash('success', 'Collection tab created successfully.');

        // Log::info('Before redirect');

        return redirect()->route('admin.collection.edit', $collection->id);
    }

    public function update(Collection $collection, $id)
    {
        $tab = $this->collectionTabRepository->findOrFail($id);

        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'required',
                'max:255',
                Rule::unique('collection_tabs', 'slug')
                    ->ignore($tab->id)
                    ->where('collection_id', $collection->id),
            ],

            'description' => ['nullable', 'string'],

            'sort_order' => ['nullable', 'integer'],

            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = request()->has('is_active');

        $tab->update($data);

        if (request()->has('categories')) {
            $tab->categories()->sync(request('categories'));
        }
        
        session()->flash('success', 'Collection tab updated successfully.');

        return redirect()->route('admin.collection.edit', $collection->id);
    }

    public function destroy(Collection $collection, $id)
    {
        $tab = $this->collectionTabRepository->findOrFail($id);

        $tab->delete();

        session()->flash('success', 'Collection tab deleted successfully.');

        return redirect()->route('admin.collection.edit', $collection->id);
    }
}