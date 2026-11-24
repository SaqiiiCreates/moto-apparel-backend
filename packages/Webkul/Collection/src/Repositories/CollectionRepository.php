<?php

namespace Webkul\Collection\Repositories;

use Webkul\Core\Eloquent\Repository;

class CollectionRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\Collection\Contracts\Collection';
    }
}