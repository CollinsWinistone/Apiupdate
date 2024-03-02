<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomStatus extends Model
{
    use PaginatorTrait, ClassRoomStatusFilter, SortTrait;

    protected $table = 'classroom_status';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'classroom_id', 'crm_id');
    }
}
