<?php

namespace Webkul\Banner\Repositories;

use Webkul\Core\Eloquent\Repository;

class BannerRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\Banner\Contracts\Banner';
    }
}