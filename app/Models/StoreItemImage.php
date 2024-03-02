<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemImage extends Model
{
    protected $table = 'store_item_images';

    const COURSE    = 1;
    const BOOK      = 4;
    const PRINTABLE = 7;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];
}
