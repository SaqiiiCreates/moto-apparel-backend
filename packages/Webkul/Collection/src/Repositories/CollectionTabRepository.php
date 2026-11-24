<?php

namespace Webkul\Collection\Repositories;

use Webkul\Core\Eloquent\Repository;

class CollectionTabRepository extends Repository
{
    public function model(): string
    {
        return 'Webkul\Collection\Contracts\CollectionTab';
    }
}