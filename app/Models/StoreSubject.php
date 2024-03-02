<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSubject extends Model
{
    protected $table = 'subjects';

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
