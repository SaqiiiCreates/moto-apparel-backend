<?php

namespace Webkul\Slider\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Slider\Repositories\SliderRepository;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function __construct(protected SliderRepository $sliderRepository) {}

    public function show($slug)
    {
        // Eager load only active slides ordered by sort_order
        $slider = $this->sliderRepository->findOneByField('slug', $slug);

        if (! $slider || ! $slider->is_active) {
            return response()->json(['message' => 'Slider not found'], 404);
        }

        return response()->json([
            'data' => [
                'id'     => $slider->id,
                'title'  => $slider->title,
                'slug'   => $slider->slug,
                'slides' => $slider->slides()
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->get()
                    ->map(function ($slide) {
                        return [
                            'id'               => $slide->id,
                            'heading'          => $slide->heading,
                            'subheading'       => $slide->subheading,
                            'description'      => $slide->description,
                            'image_url'        => Storage::url($slide->media_path),
                            'mobile_image_url' => $slide->mobile_media_path ? Storage::url($slide->mobile_media_path) : null,
                            'button_text'      => $slide->button_text,
                            'button_link'      => $slide->button_link,
                            'sort_order'       => $slide->sort_order,
                            'focal_x'          => $slide->focal_x ?? 50,
                            'focal_y'          => $slide->focal_y ?? 50,
                        ];
                    }),
            ],
        ]);
    }
}