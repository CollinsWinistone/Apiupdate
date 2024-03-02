<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomAverage extends Model
{
    protected $table = 'student_classroom_average';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'crs_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'classroom_id', 'crm_id')->withoutGlobalScopes();
    }

    public function scopeByUser($query, User $user)
    {
        if ($user->isTeacher()) {
            $query->join('classroom_teacher', function ($join) use ($user) {
                $join->on('classroom_teacher.classroom', '=', 'student_classroom_average.classroom_id')
                    ->where('classroom_teacher.teacher', $user->getKey());
            });
        }
        return $query;
    }
}
