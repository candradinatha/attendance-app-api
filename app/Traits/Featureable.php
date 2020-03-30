<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Featureable {

    public function featured()
    {
        if ($this->{$this->getFeaturedColumn()}) {
            $this->{$this->getFeaturedColumn()} = false;
        } else {
            $this->{$this->getFeaturedColumn()} = true;
        }

        $this->save();
    }

    public function getFeaturedColumn()
    {
        return defined('static::featuredField') ? static::featuredField : 'is_featured';
    }

    public function scopeIsFeatured($query)
    {
        $query->where($this->getFeaturedColumn(), true);
    }

    public function scopeWithoutFeatured($query)
    {
        $query->where($this->getFeaturedColumn(), false);
    }
}