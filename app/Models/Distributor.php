<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    const DEFAULT_ID = 1;

    public function scopeInCountry(Builder $query, $country)
    {
        return $query->where('distribution_countries','like',"%$country%");
    }

    public function scopeDefault(Builder $query)
    {
        $query->find(self::DEFAULT_ID);
    }
}
