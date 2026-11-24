<?php

namespace Webkul\Page\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Webkul\Page\Repositories\PageRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    /**
     * List pages
     */
    public function index()
    {
        $pages = $this->pageRepository->all();

        return view('page::admin.index', compact('pages'));
    }

    /**
     * Create page form
     */
    public function create()
    {
        return view('page::admin.create');
    }

    /**
     * Store page
     */
    public function store()
    {
        $data = request()->validate([
            'title'     => 'required|string',
            'slug'      => 'required|unique:pages,slug',
            'is_active' => 'nullable|boolean',
            'is_home'   => 'nullable|boolean',
        ]);

        $data['is_active'] = request()->boolean('is_active');
        $data['is_home']   = request()->boolean('is_home');

        $page = $this->pageRepository->create($data);

        session()->flash('success', 'Page created successfully.');

        return redirect()->route('admin.pages.edit', $page->id);
    }

    /**
     * Edit page
     */
    public function edit($id)
    {
        $page = $this->pageRepository->findOrFail($id);

        // eager load builder structure
        $page->load([
            'sections'
        ]);

        return view('page::admin.edit', compact('page'));
    }

    /**
     * Update page
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'        => 'required|string',
            'slug'         => 'required|unique:pages,slug,' . $id,
            'is_active'    => 'nullable|boolean',
            'banner_image' => 'nullable|image|max:3048',
            'banner_text_position' => 'nullable|in:top,middle,bottom',
            'banner_text_position_mobile' => 'nullable|in:top,middle,bottom',
            'banner_focal_x' => 'nullable|numeric|min:0|max:100',
            'banner_focal_y' => 'nullable|numeric|min:0|max:100',
        ]);        
        
        $data['is_active'] = $request->boolean('is_active');        

        $page = $this->pageRepository->findOrFail($id);        
        
        if ($request->hasFile('banner_image')) {

            if (!empty($page->banner_image)) {
                Storage::disk('public')->delete($page->banner_image);
            }

            $data['banner_image'] = $request->file('banner_image')
                ->store('pages/banners', 'public');
        }

        $data['banner_text_position'] = $request->input('banner_text_position');
        $data['banner_text_position_mobile'] = $request->input('banner_text_position_mobile');
        $data['banner_focal_x'] = $request->input('banner_focal_x', 50);
        $data['banner_focal_y'] = $request->input('banner_focal_y', 50);
        
        $this->pageRepository->update($data, $id);

        return back()->with('success', 'Page updated successfully.');
    }

    /**
     * Delete page
     */
    public function destroy($id)
    {
        $this->pageRepository->delete($id);

        session()->flash('success', 'Page deleted successfully.');

        return redirect()->route('admin.pages.index');
    }
}