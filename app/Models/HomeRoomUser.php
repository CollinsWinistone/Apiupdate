<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeRoomUser extends Model
{
    protected $table = 'homeroom_users';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public function homeroom()
    {
        return $this->belongsTo(HomeRoom::class, 'homeroom', 'id');
    }
}
