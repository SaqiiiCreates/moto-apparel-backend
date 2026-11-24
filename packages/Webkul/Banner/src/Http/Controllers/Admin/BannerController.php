<?php

namespace Webkul\Banner\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Webkul\Banner\Repositories\BannerRepository;

class BannerController extends Controller
{
    public function __construct(protected BannerRepository $bannerRepository) 
    {
    }

    public function index()
    {
        $banners = $this->bannerRepository->all();
        return view('banner::admin.index', compact('banners'));
    }

    public function create()
    {
        return view('banner::admin.create');
    }

    public function store()
    {
        $data = request()->validate([
            'title'             => 'required',
            'slug'              => 'required|unique:banner,slug',
            'preheading'        => 'nullable',
            'heading'           => 'nullable',
            'description'       => 'nullable',
            'sort_order'        => 'nullable|numeric',
            'buttons'           => 'nullable',
            'hotspots'          => 'nullable',
            'media_path'        => 'image',
            'mobile_media_path' => 'image',
        ]);

        $this->handleFileUploads($data);

        // Ensure JSON fields are handled correctly
        $data['buttons'] = json_decode(request('buttons'), true) ?: [];
        $data['hotspots'] = json_decode(request('hotspots'), true) ?: [];
        $data['is_active'] = request('is_active') ? 1 : 0;

        $this->bannerRepository->create($data);

        session()->flash('success', 'Banner created successfully.');
        return redirect()->route('admin.banners.index');
    }

    public function edit($id)
    {
        $banner = $this->bannerRepository->findOrFail($id);
        return view('banner::admin.edit', compact('banner'));
    }

    public function update($id)
    {
        $data = request()->validate([
            'title'             => 'required',
            'slug'              => 'required|unique:banner,slug,' . $id,
            'preheading'        => 'nullable',
            'heading'           => 'nullable',
            'description'       => 'nullable',
            'sort_order'        => 'nullable|numeric',
            'buttons'           => 'nullable',
            'hotspots'          => 'nullable',
            'media_path'        => 'nullable|image',
            'mobile_media_path' => 'nullable|image',
        ]);

        $this->handleFileUploads($data);

        $data['buttons'] = json_decode(request('buttons'), true) ?: [];
        $data['hotspots'] = json_decode(request('hotspots'), true) ?: [];
        $data['is_active'] = request('is_active') ? 1 : 0;

        $this->bannerRepository->update($data, $id);

        session()->flash('success', 'Banner updated successfully.');
        return redirect()->route('admin.banners.index');
    }

    public function destroy($id)
    {
        $this->bannerRepository->delete($id);
        session()->flash('success', 'Banner deleted successfully.');
        return redirect()->route('admin.banners.index');
    }

    protected function handleFileUploads(&$data)
    {
        if (request()->hasFile('media_path')) {
            $data['media_path'] = request()->file('media_path')->store('banners');
        }

        if (request()->hasFile('mobile_media_path')) {
            $data['mobile_media_path'] = request()->file('mobile_media_path')->store('banners');
        }
    }
}