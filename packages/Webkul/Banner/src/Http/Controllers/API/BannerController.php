<?php

namespace Webkul\Banner\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Banner\Repositories\BannerRepository;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct(protected BannerRepository $bannerRepository) {}

    public function show($slug)
    {
        $banner = $this->bannerRepository->findOneByField('slug', $slug);

        if (! $banner || ! $banner->is_active) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        return response()->json([
            'data' => [
                'id'                => $banner->id,
                'slug'              => $banner->slug,
                'heading'           => $banner->heading,
                'subheading'        => $banner->preheading,
                'description'       => $banner->description,
                'desktopSrc'        => $banner->media_path ? Storage::url($banner->media_path) : null,
                'mobileSrc'         => $banner->mobile_media_path ? Storage::url($banner->mobile_media_path) : null,
                'hotspots'          => $banner->hotspots ?? [],
                'buttons'           => $banner->buttons ?? [],
            ]
        ]);
    }
}