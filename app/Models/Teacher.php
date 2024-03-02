<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Teacher extends User
{
    const ROLE = 'teacher';
    const ROLE_ID = 4;

    protected $guarded = [];

    public function classrooms()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'classroom_teacher',
            'teacher',
            'classroom',
            'usr_id',
            'crm_id'
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new TeacherObserver());
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        if ($value->isPrincipal() || $value->isSuperIntendent() || $value->isPrivateTeacher()) {
            return $query->bySchool();
        }

        if ($value->isParent()) {
            return $query->byParentId($value->getKey());
        }
    }

    public function scopeByParentId($query, $value)
    {
        $studentsIds = DB::table('student_parents')
                ->where('accepted', 1)
                ->where('parent_usr_id', $value)
                ->pluck('student_usr_id')->toArray();

        $classRoomIds = DB::table('classroom_student')->whereIn('enr_user', $studentsIds)->pluck('enr_classroom')->toArray();
        $homeRoomIds  = DB::table('homeroom_users')->whereIn('users_id', $studentsIds)->pluck('homeroom_id')->toArray();

        if (!$classRoomIds && !$homeRoomIds) {
            $query->whereNull('usr_id');
        }

        $query->where( function( $subquery ) use ( $classRoomIds, $homeRoomIds ) {
            if ($classRoomIds) {
                $subquery->orWhereHas('classrooms', function($q) use ($classRoomIds) {
                    $q->whereIn('crm_id', $classRoomIds);
                });
            }

            if ($homeRoomIds) {
                $subquery->orWhereHas('homeroomUsers', function($q) use ($homeRoomIds) {
                    $q->whereIn('homeroom_id', $homeRoomIds);
                });
            }
        });

        return $query;
    }
}
