<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    const SHIPPING = 1;
    const BILLING = 2;
    const SHIPPING_AND_BILLING = 3;

    protected $guarded = [];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeBilling(Builder $query)
    {
        return $query->where('type', self::BILLING);
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeShipping(Builder $query)
    {
        return $query->where('type', self::SHIPPING);
    }


}
