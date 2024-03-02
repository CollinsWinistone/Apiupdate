<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreTarget extends Model
{
    protected $table = 'targets';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();
    }
}
