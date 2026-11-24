<?php

namespace Webkul\Page\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Page\Repositories\PageRepository;

use Webkul\Page\Models\Page;
use Webkul\Page\Models\PageSection;

class PageSectionController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    public function store(Request $request, Page $page)
    {
        $page->sections()->create([
            'type' => $request->type ?? 'content',
            'content' => '',
            'sort_order' => $page->sections()->count(),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Page $page, PageSection $section)
    {
        if ($section->page_id !== $page->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $section->update([
            'content' => request('content')
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy(Page $page, PageSection $section)
    {
        try {
            if ($section->page_id !== $page->id) {
                return response()->json([
                    'message' => 'Unauthorized section access'
                ], 403);
            }

            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Section deleted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}