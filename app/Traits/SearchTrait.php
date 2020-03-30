<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Libraries\SearchScope;

trait SearchTrait {

    public static function bootSearchTrait()
    {
        static::addGlobalScope(new SearchScope);
    }

    public function getSearchAttribute()
    {
        return $this->searchAttribute;
    }
}