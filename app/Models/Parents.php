<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Parents extends User
{
    const ROLE = 'parents';
    const ROLE_ID = 2;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new ParentObserver);
    }

    public function allStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_parents',
            'parent_usr_id',
            'student_usr_id',
            'usr_id',
            'usr_id'
        )->withoutGlobalScopes()->where('declined', 0)->withTimestamps();
    }

    public function students()
    {
        return $this->allStudents()->where('accepted', 1);
    }

    public function studentRequests()
    {
        return $this->hasMany(
            StudentParent::class,
            'parent_usr_id',
            'usr_id'
        );
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isSuperIntendent()) {
            return ($this->schools()->whereIn('sch_id', $user->getSchoolIds())->count() > 0);
        }
        if ($user->isPrincipal() || $user->isTeacher() || $user->isPrivateTeacher()) {
            return ($this->schools()->whereSchId($user->getSchool()->getKey())->count() > 0);
        }
        return FALSE;
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
        if ($value->isTeacher()) {
            return $query->bySchool()->byTeacherId($value->getKey());
        }
    }

    public function scopeByTeacherId($query, $value)
    {
        $classRoomIds = DB::table('classroom_teacher')->where('teacher', $value)->pluck('classroom')->toArray();
        $homeRoomIds  = DB::table('homeroom_users')->where('users_id', $value)->pluck('homeroom_id')->toArray();

        $studentIds = [];

        if ($classRoomIds) {
            $studentIds = DB::table('classroom_student')->whereIn('enr_classroom', $classRoomIds)->pluck('enr_user')->toArray();
        }

        if ($homeRoomIds) {
            $studentIds = array_merge(
                $studentIds,
                DB::table('homeroom_users')
                    ->where('homeroom_id', $homeRoomIds)
                    ->where('groups_id', Student::ROLE_ID)
                    ->pluck('users_id')
                    ->toArray()
            );
        }

        $query->whereHas('students', function($q) use ($studentIds) {
            $q->whereIn('student_usr_id', $studentIds);
        });
        return $query;
    }
}
