<?php
namespace Webkul\Slider\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Webkul\Slider\Models\Slide; 
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    public function create($slider_id)
    {
        return view('slider::admin.slides.create', compact('slider_id'));
    }

    public function store($slider_id)
    {
        // 1. Validation must match the 'name' attributes in your Blade file
        request()->validate([
            'media_path' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'button_link' => 'nullable|string',
            'heading'     => 'nullable|string',
            'subheading'  => 'nullable|string',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string',
        ]);

        // 2. Handle File Upload
        $path = request()->file('media_path')->store('sliders/' . $slider_id, 'public');

        // 3. Create using the exact $fillable fields from your Model
        Slide::create([
            'slider_id'   => $slider_id,
            'media_path'  => $path,
            'heading'     => request('heading'),
            'subheading'  => request('subheading'),
            'description' => request('description'),
            'button_text' => request('button_text'),
            'button_link' => request('button_link'),
            'sort_order'  => request('sort_order') ?? 0,
            'is_active'   => request()->has('is_active') ? 1 : 0,
            'type'        => 'image', // Adding a default if your model requires it
        ]);

        session()->flash('success', 'Slide added successfully.');
        return redirect()->route('admin.slider.edit', $slider_id);
    }

    public function edit($slider_id, $id)
    {
        $slide = Slide::findOrFail($id);
        return view('slider::admin.slides.edit', compact('slider_id', 'slide'));
    }

    public function update($slider_id, $id)
    {
        $slide = Slide::findOrFail($id);

        $data = request()->validate([
            'media_path'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'heading'     => 'nullable|string',
            'subheading'  => 'nullable|string',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'focal_x'     => 'nullable|numeric|min:0|max:100',
            'focal_y'     => 'nullable|numeric|min:0|max:100',
            'is_active'   => 'nullable',
        ]);

        if (request()->hasFile('media_path')) {
            if (
                $slide->media_path &&
                Storage::disk('public')->exists($slide->media_path)
            ) {
                Storage::disk('public')->delete($slide->media_path);
            }

            $slide->media_path = request()
                ->file('media_path')
                ->store('sliders/' . $slider_id, 'public');
        }

        $slide->heading     = $data['heading'] ?? null;
        $slide->subheading  = $data['subheading'] ?? null;
        $slide->description = $data['description'] ?? null;
        $slide->button_text = $data['button_text'] ?? null;
        $slide->button_link = $data['button_link'] ?? null;

        $slide->sort_order = $data['sort_order'] ?? 0;
        $slide->is_active  = request()->boolean('is_active') ? 1 : 0;

        $slide->focal_x = $data['focal_x'] ?? 50;
        $slide->focal_y = $data['focal_y'] ?? 50;

        $slide->save();

        session()->flash('success', 'Slide updated successfully.');

        return redirect()->route('admin.slider.edit', $slider_id);
    }

    public function destroy($slider_id, $id)
    {
        $slide = Slide::findOrFail($id);
        Storage::disk('public')->delete($slide->media_path);
        $slide->delete();

        session()->flash('success', 'Slide deleted.');
        return redirect()->route('admin.slider.edit', $slider_id);
    }
}