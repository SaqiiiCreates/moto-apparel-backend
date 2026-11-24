<?php

namespace Webkul\Slider\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Slider\Contracts\Slider as SliderContract;

class Slider extends Model implements SliderContract
{
    protected $fillable = [
        'title',        
        'heading',        
        'description',
        'slug',
        'short_code',
        'is_active',
        'sort_order'
    ];

    protected $table = 'sliders';

    /**
     * A slider has many slides
     */
    public function slides()
    {
        return $this->hasMany(Slide::class);
    }
}