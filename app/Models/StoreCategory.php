<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    protected $table = 'store_categories';

    const COURSE    = 1;
    const BOOK      = 4;
    const PRINTABLE = 7;
    const POINTS    = 8;

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
