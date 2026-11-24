<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingTemplateOptions
{
    public static function all(): array
    {
        $options = [
            [
                'title' => 'Please select',
                'value' => '',
            ],
        ];

        try {
            $templates = DB::table('marketing_templates')
                ->orderBy('name')
                ->get(['id', 'name']);

            foreach ($templates as $template) {
                $options[] = [
                    'title' => $template->name,
                    'value' => (string) $template->id,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Waitlist template options failed: '.$e->getMessage());
        }

        return $options;
    }
}