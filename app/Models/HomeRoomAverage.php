<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeRoomAverage extends Model
{
    protected $table = 'homeroom_average';

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public function homeroom()
    {
        return $this->belongsTo(HomeRoom::class, 'homeroom_id', 'id');
    }
}
