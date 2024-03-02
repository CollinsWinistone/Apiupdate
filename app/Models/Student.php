<?php
namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Student extends User
{
    use ArchiveTrait;

    const ROLE = 'student';
    const ROLE_ID = 3;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new StudentObserver);
    }

    public function autoguide()
    {
        return $this->hasOne(
            StudentAutoguide::class,
            'user_id',
            'usr_id'
        );
    }

    public function classrooms()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'classroom_student',
            'enr_user',
            'enr_classroom',
            'usr_id',
            'crm_id'
        )->whereNull('archived_at');
    }

    public function enrClassrooms()
    {
        return $this->hasMany(
            ClassRoomStudent::class,
            'enr_user',
            'usr_id'
        );
    }

    public function subscriptions()
    {
        return $this->hasMany(
            UserSubscriptionStudent::class,
            'student_id',
            'usr_id'
        );
    }

    public function homeroomUsers()
    {
        return $this->hasMany(
            HomeRoomUser::class,
            'users_id',
            'usr_id'
        );
    }

    public function parentRequests()
    {
        return $this->hasMany(
            StudentParent::class,
            'student_usr_id',
            'usr_id'
        );
    }

    public function parents()
    {
        return $this->belongsToMany(
            Parents::class,
            'student_parents',
            'student_usr_id',
            'parent_usr_id',
            'usr_id',
            'usr_id'
        )->where('accepted', true)->withTimestamps();
    }

    public function getName()
    {
        return $this->getFullName();
    }

    public function isAutomated()
    {
        return (bool) $this->autoguide ? $this->autoguide->is_automated : false;
    }

    /**
     * @param $query
     * @param $homeroomId
     * @return mixed
     */
    public function scopeByHomeroomId($query, $homeroomId)
    {
        return $query->whereHas('homeroomUsers', function ($parentQuery) use ($homeroomId){
            $parentQuery->where('homeroom_id', $homeroomId);
        });
    }

    /**
     * @param $query
     * @param $classroomId
     * @return mixed
     */
    public function scopeByClassroomId($query, $classroomId)
    {
        return $query->whereHas('enrClassrooms', function ($parentQuery) use ($classroomId){
            $parentQuery->where('enr_classroom', $classroomId);
        });
    }

    /**
     * @param $query
     * @param $parentId
     * @return mixed
     */
    public function scopeByParentId($query, $parentId)
    {
        return $query->whereHas('parents', function ($parentQuery) use ($parentId){
            $parentQuery->where('usr_id', $parentId);
        });
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByTeacherId($query, $value)
    {
        $classRoomIds = DB::table('classroom_teacher')->where('teacher', $value)->pluck('classroom')->toArray();
        $homeRoomIds  = DB::table('homeroom_users')->where('users_id', $value)->where('groups_id', 4)->pluck('homeroom_id')->toArray();

        if (!$classRoomIds && !$homeRoomIds) {
            $query->whereNull('usr_id');
        }

        $query->where( function( $subquery ) use ( $classRoomIds, $homeRoomIds ) {
            if ($classRoomIds) {
                $subquery->orWhereHas('classrooms', function($q) use ($classRoomIds) {
                    $q->whereIn('crm_id', $classRoomIds);
                    $q->whereNull('archived_at');
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

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        if ($value->isPrincipal() || $value->isSuperIntendent() || $value->isPrivateTeacher()|| $value->isAssistant()) {
            return $query->bySchool();
        }
        if ($value->isTeacher()) {
            return $query->bySchool()->byTeacherId($value->getKey());
        }
        if ($value->isParent()) {
            return $query->byParentId($value->getKey());
        }
    }

    public function currentAttempts()
    {
        return $this->hasMany(
            ClassRoomAttemptsCurrent::class,
            'student_id',
            'usr_id'
        );
    }

    public function attempts()
    {
        return $this->hasMany(
            ClassRoomAttempts::class,
            'student_id',
            'usr_id'
        );
    }

    public function rewards()
    {
        return $this->hasMany(
            UserReward::class,
            'student_id',
            'usr_id'
        );
    }

    public function classroomAverages()
    {
        return $this->hasMany(
            ClassRoomAverage::class,
            'student_id',
            'usr_id'
        );
    }

    public function units()
    {
        return $this->hasMany(
            ClassRoomUnitStatus::class,
            'student_id',
            'usr_id'
        );
    }

    public function hasActiveCourse(Course $course, Seria $seria = null)
    {
        $query = $this->classrooms()
                ->where('crm_course', $course->getKey())
                ->where('crm_end_date', '>=', Carbon::now()->toDateString())
                ->whereNull('archived_at')
                ->bySeria($seria);

        return (bool) $query->count();
    }

    /**
     * @param $query
     * @param ClassRoom $classRoom
     * @return mixed
     */
    public function scopeNotInClassroom($query, ClassRoom $classRoom)
    {
        return $query->whereNotIn('users.usr_id', $classRoom->students()->pluck('enr_user')->toArray());
    }

    /**
     * @param $query
     * @param ClassRoom $classRoom
     * @return mixed
     */
    public function scopeNotInCurrentAttempts($query, ClassRoom $classRoom)
    {
        return $query->whereNotIn('users.usr_id', $classRoom->attemptsCurrent()->pluck('student_id')->toArray());
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parents()->first();
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isParent()) {
            return $this->parents()->where('usr_id', $user->getKey())->exists();
        }
        if ($user->isPrincipal() || $user->isTeacher() || $user->isPrivateTeacher() || $user->isAssistant()) {
            return ($user->getSchool()->getKey() == $this->getSchool()->getKey());
        }
        if ($user->isSuperIntendent()) {
            return in_array($this->getSchool()->getKey(), $user->getSchoolIds());
        }
        if ($user->isTeacher()) {
            // Add rules based on classrooms/homerooms
            return TRUE;
        }
        if ($user->isStudent() && $user->getKey() == $this->getKey()) {
            return TRUE;
        }
        return FALSE;
    }

    public function toArchive()
    {
        $data = array_filter($this->toArray());

        $data['schools'] = [];

        $data['attempts'] = $this->attempts()
                ->select('att_date', 'classroom_id', 'lesson_id', 'lesson_inst_id', 'unit_id', 'scored_points', 'lesson_points', 'pass_weight', 'pass', 'metadata', 'attempt_duration')
                ->getResults()
                ->toArray();

        $data['currentAttempts'] = $this->currentAttempts()
                ->select('att_date', 'att_close', 'att_next', 'attempt_no', 'classroom_id', 'lesson_id', 'lesson_inst_id', 'unit_id', 'scored_points', 'lesson_points', 'pass_weight', 'pass', 'metadata', 'attempt_duration', 'lesson_accessible', 'att_permitted')
                ->getResults()
                ->toArray();

        $data['unitStatuses'] = $this->units()
                ->select('course_id', 'classroom_id', 'status', 'grade', 'date_updated', 'units_name')
                ->getResults()
                ->toArray();

        $enrClassrooms = $this->enrClassrooms()->with(['classroom', 'classroom.course'])->getResults();

        foreach ($enrClassrooms as $enrClassroom) {
            $data['classrooms'][] = [
                'classroom_id'          => $enrClassroom->enr_classroom,
                'classroom_name'        => $enrClassroom->classroom->crm_name,
                'course_id'             => $enrClassroom->classroom->crm_course,
                'course_name'           => $enrClassroom->classroom->course->crs_title,
                'course_template'       => $enrClassroom->classroom->course->course_template,
                'enr_date'              => $enrClassroom->enr_date,
                'user_subscription_id'  => $enrClassroom->user_subscription_id,
                'series_id'             => $enrClassroom->series_id,
                'last_active'           => $enrClassroom->last_active
            ];
        }

        $data['classroomAverages'] = $this->classroomAverages()
                ->select('sca_date', 'classroom_id', 'completed_progress', 'inprogress_progress', 'total_possible_point', 'scored_points', 'average_grade', 'pass_rate')
                ->getResults()
                ->toArray();

        foreach ($this->homeroomUsers()->getResults() as $homeroom) {
            $data['homerooms'][] = $homeroom->homeroom_id;
        }

        foreach ($this->schools()->getResults() as $school) {
            $data['schools'][] = $school->sch_id;
        }

        foreach ($this->rewards()->getResults() as $reward) {
            $data['rewards'][] = array_filter($reward->toArray());
        }

        foreach ($this->parents()->getResults() as $parents) {
            $data['parents'][] = $parents->getKey();
        }

        foreach ($this->subscriptions()->getResults() as $subscription) {
            $data['subscriptions'][] = array_filter($subscription->toArray());
        }

        return $data;
    }
}
