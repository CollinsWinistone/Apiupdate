<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClassRoom extends Model
{
    use PaginatorTrait, ClassRoomFilter, ClassRoomSorter, AvatarTrait, ArchiveTrait;

    public $timestamps = false;

    protected $table = 'classrooms';

    protected $primaryKey = 'crm_id';

    protected $_defaultAvatar = 'https://bzabc.s3.ap-southeast-1.amazonaws.com/default-avatars/classroom.png';

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

        static::addGlobalScope(function ($query) {
            return $query->whereNull('archived_at');
        });

        static::observe(new ClassroomObserver());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'crm_school', 'sch_id');
    }

    /**
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'crm_course', 'crs_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seria()
    {
        return $this->belongsTo(Seria::class, 'crm_series', 'series_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attempts()
    {
        return $this->hasMany(ClassRoomAttempts::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'inv_crm_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptionStudents()
    {
        return $this->hasMany(UserSubscriptionStudent::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history()
    {
        return $this->hasMany(ClassRoomHistory::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classroomStudents()
    {
        return $this->hasMany(ClassRoomStudent::class, 'enr_classroom', 'crm_id');
    }

    public function classroomStudent($studentId)
    {
        return $this->classroomStudents()->where('enr_user', $studentId)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attemptsCurrent()
    {
        return $this->hasMany(ClassRoomAttemptsCurrent::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function averages()
    {
        return $this->hasMany(ClassRoomAverage::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(ClassRoomStatus::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unitStatuses()
    {
        return $this->hasMany(ClassRoomUnitStatus::class, 'classroom_id', 'crm_id');
    }

    public function openInvoices()
    {
        return $this->hasMany(OpenInvoice::class,'classroom_id','crm_id');
    }

    public function shoppingCart()
    {
        return $this->hasMany(ShoppingCartItem::class,'classroom_id','crm_id');
    }

    /**
     * @return $this
     */
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'classroom_teacher',
            'classroom',
            'teacher'
        )->withoutGlobalScopes();
    }

    /**
     * For classroom which has one teacher
     * @return mixed
     */
    public function getTeacher()
    {
        return $this->teachers()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function homerooms()
    {
        return $this->belongsToMany(
            HomeRoom::class,
            'classroom_homerooms',
            'classroom_id',
            'homeroom_id'
        );
    }

    /**
     * For classroom which has one homeroom
     * @return mixed
     */
    public function getHomeroom()
    {
        return $this->homerooms()->first();
    }

    /**
     * @return $this
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'classroom_student',
            'enr_classroom',
            'enr_user'
        )->withoutGlobalScopes();
    }

    public function getName()
    {
        return $this->crm_name;
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeBySchool(Builder $query)
    {
        if (Auth::user()->isSuperIntendent()) {
            return $query->whereHas('school', function ($schools) {
                $schools->whereIn('sch_id', Auth::user()->getSchoolIds());
            });
        }
        return $query->whereHas('school', function ($schools) {
            $schools->where('sch_id', Auth::user()->getSchoolId());
        });
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeNotBySchool(Builder $query)
    {
        return $query->whereHas('school', function ($schools) {
            $schools->where('sch_id', 0)->orWhere('sch_id', null);
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeByDemo(Builder $query)
    {
        return $query->whereHas('course', function (Builder $query) {
            $query->where('is_demo', '=', Course::IS_DEMO);
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeByNotDemo(Builder $query)
    {
        return $query->whereHas('course', function (Builder $query) {
            $query->where('is_demo', '<>', Course::IS_DEMO);
        });
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeBySeria(Builder $query, Seria $seria = null)
    {
        if ($seria) {
            return $query->where('crm_series', $seria->getKey());
        }
        return $query->whereNull('crm_series');
    }

    /**
     * @param Builder $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeByAvailablePublicClassroom(Builder $query)
    {
        return $query->whereDate('crm_enrollment_end_date', '>', Carbon::now());
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeByTeacherId(Builder $query, $value)
    {
        return $query->whereHas('teachers', function ($teachers) use ($value) {
            $teachers->where('usr_id', $value);
        });
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeByUser(Builder $query, $value)
    {
        if ($value->isAdmin()) {
            return $query->whereNotNull('crm_school');
        }
        if ($value->isPrincipal() || $value->isSuperIntendent() || $value->isPrivateTeacher() || $value->isAssistant()) {
            return $query->bySchool();
        }
        if ($value->isTeacher()) {
            return $query->bySchool()->byTeacherId($value->getKey());
        }
    }

    public function refund($studentIds)
    {
        $inventories = $this->inventoryItems()->whereIn('student_id', $studentIds)->getResults();

        foreach ($inventories as $inventory) {
            $inventory->refund();
        }
    }

    public function isStarted()
    {
        return (Carbon::now()->toDateString() >= Carbon::parse($this->crm_start_date)->toDateString());
    }

    public function isPaid()
    {
        $studentIds = $this->students->pluck('usr_id')->toArray();
        if (!$studentIds) {
            return true;
        }
        return !$this->attemptsCurrent()->whereIn('student_id', $studentIds)->where('lesson_accessible', 0)->exists();
    }

    public function isActive()
    {
        return $this->archived_at ? false : true;
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isSuperIntendent()) {
            return in_array($this->crm_school, $user->getSchoolIds());
        }
        if ($user->isPrincipal() || $user->isPrivateTeacher() || $user->isAssistant()) {
            return ($user->getSchool()->getKey() == $this->crm_school);
        }
        if ($user->isTeacher()) {
            return $this->teachers()->where('usr_id', $user->getKey())->exists();
        }
        return FALSE;
    }

    public function delete()
    {
        $this->archive();

        if (!$this->students()->count()) {
            return parent::delete();
        }

        $this->archived_at = Carbon::now()->toDateTimeString();

        $this->invitations()->where('inv_accepted', Invitation::STATUS_PENDING)->update(['inv_accepted' => Invitation::STATUS_EXPIRED]);
        $this->attempts()->delete();
        $this->attemptsCurrent()->delete();
        $this->unitStatuses()->delete();
        $this->openInvoices()->delete();
        $this->shoppingCart()->delete();

        foreach ($this->subscriptionStudents as $student) {
            $student->delete();
        }

        return $this->save();
    }

    public function toArchive()
    {
        $data = array_filter($this->toArray());

        $data['crm_course_name']     = $this->course->crs_title;
        $data['crm_course_template'] = $this->course->course_template;

        $data['attempts'] = $this->attempts()
                ->select('att_date', 'student_id', 'lesson_id', 'lesson_inst_id', 'unit_id', 'scored_points', 'lesson_points', 'pass_weight', 'pass', 'metadata', 'attempt_duration')
                ->getResults()
                ->toArray();

        $data['currentAttempts'] = $this->attemptsCurrent()
                ->select('att_date', 'att_close', 'att_next', 'attempt_no', 'student_id', 'lesson_id', 'lesson_inst_id', 'unit_id', 'scored_points', 'lesson_points', 'pass_weight', 'pass', 'metadata', 'attempt_duration', 'lesson_accessible', 'att_permitted')
                ->getResults()
                ->toArray();

        $data['studentAverages'] = $this->averages()
                ->select('sca_date', 'student_id', 'completed_progress', 'inprogress_progress', 'total_possible_point', 'scored_points', 'average_grade', 'pass_rate')
                ->getResults()
                ->toArray();

        $data['statuses'] = $this->statuses()
                ->select('cs_date', 'completed_progress', 'inprogress_progress', 'total_possible_point', 'scored_points', 'average_grade', 'pass_rate')
                ->getResults()
                ->toArray();

        $data['unitStatuses'] = $this->unitStatuses()
                ->select('course_id', 'student_id', 'status', 'grade', 'date_updated', 'units_name')
                ->getResults()
                ->toArray();

        $classroomStudents = $this->classroomStudents()->with(['student'])->getResults();

        foreach ($classroomStudents as $classroomStudent) {
            $data['students'][] = [
                'name'                  => $classroomStudent->student->getFullName(),
                'email'                 => $classroomStudent->student->email,
                'username'              => $classroomStudent->student->username,
                'student_id'            => $classroomStudent->enr_user,
                'enr_date'              => $classroomStudent->enr_date,
                'user_subscription_id'  => $classroomStudent->user_subscription_id,
                'series_id'             => $classroomStudent->series_id,
                'last_active'           => $classroomStudent->last_active,
            ];
        }

        foreach ($this->homerooms()->getResults() as $homeroom) {
            $data['homerooms'][] = $homeroom->getKey();
        }

        return $data;
    }

    public function removeStudent(Student $student)
    {
        $this->attempts()->where('student_id', $student->getKey())->delete();
        $this->archiveAttempts($student->getKey());
        $this->unitStatuses()->where('student_id', $student->getKey())->delete();
        $this->averages()->where('student_id', $student->getKey())->delete();

        $this->students()->detach([$student->getKey()]);
    }

    public function getHash(User $user = null)
    {
        return Hash::make(
            chr($this->getKey() % 255)
            . $this->getKey()
            . $this->crm_course
            . $this->crm_created_by
            . $this->inv_crm_id
            . ($user ? (chr($user->getKey() % 255) . $user->getKey() . $user->created_at) : 0)
        );
    }

    public function archiveAttempts($studentIds)
    {
        if (!is_array($studentIds)) {
            $studentIds = [$studentIds];
        }

        $attempts = $this->attemptsCurrent()->whereIn('student_id', $studentIds)->getResults();

        $attemptsStudents = [];
        foreach ($attempts as $attempt) {
            if (!isset($attemptsStudents[$attempt->student_id])) {
                $attemptsStudents[$attempt->student_id] = [];
            }
            $attemptsStudents[$attempt->student_id][] = $attempt;
        }

        foreach ($attemptsStudents as $studentAttempts) {
            $data = [];

            $history = new ClassRoomAttemptsHistory();

            $isStarted = false;

            foreach ($studentAttempts as $studentAttempt) {
                $data[] = ClassRoomAttemptsHistory::attemptToArray($studentAttempt);
                if ($studentAttempt->att_date) {
                    $isStarted = true;
                }
            }

            if (!$isStarted) {
                continue;
            }

            $history->classroom_id      = $studentAttempt->classroom_id;
            $history->student_id        = $studentAttempt->student_id;
            $history->course_id         = $studentAttempt->course_id;
            $history->course_name       = $studentAttempt->course_name;
            $history->classroom_name    = $studentAttempt->classroom_name;
            $history->username          = $studentAttempt->username;
            $history->data              = $data;
            $history->save();
        }

        $this->attemptsCurrent()->whereIn('student_id', $studentIds)->delete();
    }
}
