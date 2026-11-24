<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Contracts\ProductWaitlist as ProductWaitlistContract;
use Webkul\Product\Models\ProductVariantProxy;
use Webkul\Customer\Models\CustomerProxy;

class ProductWaitlist extends Model implements ProductWaitlistContract
{
    protected $table = 'product_waitlists';

    protected $fillable = [
        'product_variant_id',
        'customer_id',
        'email',
        'is_notified',
    ];

    protected $casts = [
        'is_notified' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantProxy::modelClass(), 'product_variant_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}