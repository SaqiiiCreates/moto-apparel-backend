<?php

namespace Webkul\Slider\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Slider\Contracts\Slide as SlideContract;

class Slide extends Model implements SlideContract
{
    protected $fillable = [
        'slider_id',        
        'type',
        'media_path',
        'mobile_media_path',
        'heading',
        'subheading',
        'description',
        'button_text',
        'button_link',
        'is_active',
        'focal_x',
        'focal_y',
        'sort_order'
    ];

    protected $table = 'slides';

    /**
     * A slide belongs to a slider
     */
    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}