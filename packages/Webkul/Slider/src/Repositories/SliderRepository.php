<?php

namespace Webkul\Slider\Repositories;

use Webkul\Core\Eloquent\Repository;

class SliderRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\Slider\Contracts\Slider';
    }
}