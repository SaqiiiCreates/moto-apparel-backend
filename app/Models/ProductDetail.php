<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    protected $table = 'product_details';

    protected $fillable = [
        'product_id',
        'title',
        'content',
        'button_label',
        'button_link',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}