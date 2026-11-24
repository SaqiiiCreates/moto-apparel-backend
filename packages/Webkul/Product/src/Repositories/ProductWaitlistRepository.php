<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;

class ProductWaitlistRepository extends Repository
{
    /**
     * Specify the Model class name.
     */
    public function model(): string
    {
        return 'Webkul\Product\Contracts\ProductWaitlist';
    }
}