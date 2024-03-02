<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomUnitStatus extends Model
{
    protected $table = 'units_status';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];
}
