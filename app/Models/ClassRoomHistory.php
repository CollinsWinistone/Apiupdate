<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomHistory extends Model
{
    protected $table = 'classroom_history';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @param $query
     * @param int $classRoomId
     * @return mixed
     */
    public function scopeInClassroom($query, $classRoomId)
    {
        return $query->where('classroom_id', $classRoomId);
    }

    /**
     * @param $query
     * @param int $schoolId
     * @return mixed
     */
    public function scopeInSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId)->whereNull('classroom_id');
    }

    /**
     * @param $query
     * @param string $date
     * @return mixed
     */
    public function scopeDate($query, $date)
    {
        return $query->where('date', $date);
    }
}
