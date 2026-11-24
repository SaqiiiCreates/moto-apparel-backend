<?php

namespace Webkul\Page\Repositories;

use Webkul\Core\Eloquent\Repository;

class PageRepository extends Repository
{
    /**
     * Model
     */
    public function model(): string
    {
        return 'Webkul\Page\Contracts\Page';
    }

    /**
     * Get full page with sections + blocks
     */
    public function getPageBySlug(string $slug)
    {
        return $this->model
            ->with([
                'sections' => function ($q) {
                    $q->orderBy('sort_order');
                },
                'sections' => function ($q) {
                    $q->orderBy('sort_order');
                }
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Get homepage
     */
    public function getHomePage()
    {
        return $this->model
            ->with([
                'sections.blocks'
            ])
            ->where('is_home', 1)
            ->first();
    }

    /**
     * Toggle active state
     */
    public function toggleStatus($id)
    {
        $page = $this->find($id);

        $page->update([
            'is_active' => !$page->is_active
        ]);

        return $page;
    }
}