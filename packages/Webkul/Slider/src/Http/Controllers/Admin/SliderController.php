<?php

namespace Webkul\Slider\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Webkul\Slider\Repositories\SliderRepository;

class SliderController extends Controller
{
    public function __construct(protected SliderRepository $sliderRepository) 
    {
    }

    public function index()
    {
        $sliders = $this->sliderRepository->all();
        return view('slider::admin.index', compact('sliders'));
    }

    public function create()
    {
        return view('slider::admin.create');
    }

    public function store()
    {
        request()->validate([
            'title'    => 'required',
            'slug'     => 'required|unique:sliders,slug',
            'heading'  => 'nullable',
        ]);

        $data = request()->all();
        $data['is_active'] = request()->has('is_active') ? 1 : 0;

        $this->sliderRepository->create($data);

        session()->flash('success', 'Slider Group created successfully.');
        return redirect()->route('admin.slider.index');
    }

    public function edit($id)
    {
        // Load the slider with its slides to avoid N+1 issues
        $slider = $this->sliderRepository->with('slides')->findOrFail($id);
        return view('slider::admin.edit', compact('slider'));
    }

    public function update($id)
    {
        request()->validate([
            'title' => 'required',
            'slug'  => 'required|unique:sliders,slug,' . $id,
        ]);

        $data = request()->all();
        $data['is_active'] = request()->has('is_active') ? 1 : 0;

        // Update only the Slider Group details
        $this->sliderRepository->update($data, $id);

        session()->flash('success', 'Slider Group updated successfully.');
        return redirect()->route('admin.slider.edit', $id);
    }

    public function destroy($id)
    {
        $this->sliderRepository->delete($id);
        session()->flash('success', 'Slider Group deleted.');
        return redirect()->route('admin.slider.index');
    }
}