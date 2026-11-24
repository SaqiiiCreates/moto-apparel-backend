<?php

namespace Webkul\Page\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Webkul\Page\Repositories\PageRepository;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    /**
     * Get page by slug with full builder structure
     */
    public function show($slug)
    {
        $page = $this->pageRepository->getPageBySlug($slug);

        if (!$page || !$page->is_active) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id'        => $page->id,
                'title'     => $page->title,
                'slug'      => $page->slug,
                'is_home'   => $page->is_home,
                'banner_image' => $page->banner_image_url,
                'banner_focal_x' => $page->banner_focal_x ?? 50,
                'banner_focal_y' => $page->banner_focal_y ?? 50,
                'banner_text_position' => $page->banner_text_position,
                'banner_text_position_mobile' => $page->banner_text_position_mobile,
                
                /*
                |--------------------------------------------------------------------------
                | FULL PAGE STRUCTURE (CORE OF YOUR CMS)
                |--------------------------------------------------------------------------
                */
                'sections' => $page->sections->map(function ($section) {
                    return [
                        'id'         => $section->id,
                        'type'       => $section->type,
                        'content'    => $section->content,
                        'sort_order' => strip_tags($section->content),
                        'settings'   => $section->settings ?? [],
                    ];
                })->values(),
            ]
        ]);
    }
}