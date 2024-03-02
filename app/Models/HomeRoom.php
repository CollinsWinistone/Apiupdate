<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class HomeRoom extends Model
{
    use PaginatorTrait, HomeRoomFilter, HomeRoomSorter, AvatarTrait;

    protected $table = 'homeroom';

    public $timestamps = false;

    protected $_defaultAvatar = 'https://bzabc.s3.ap-southeast-1.amazonaws.com/default-avatars/homeroom.png';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'sch_id');
    }

    public function average()
    {
        return $this->hasOne(
            HomeRoomAverage::class,
            'homeroom_id',
            'id'
        );
    }

    /**
     * @return $this
     */
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'homeroom_users',
            'homeroom_id',
            'users_id'
        )->roleId(Teacher::ROLE_ID);
    }

    /**
     * @return $this
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'homeroom_users',
            'homeroom_id',
            'users_id'
        )->roleId(Student::ROLE_ID);
    }

    /**
     * Homeroom should have only one teacher
     * @return Teacher
     */
    public function getTeacher()
    {
        return $this->teachers->first();
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeBySchool(Builder $query, $schoolId = null)
    {
        if (!$schoolId) {
            if (Auth::user()->isAdmin()) {
                return $query;
            }
            $schoolId = Auth::user()->isSuperIntendent() ? Auth::user()->getSchoolIds() : Auth::user()->getSchoolId();
        }

        return $query->whereHas('school', function ($schools) use ($schoolId) {
            if (is_array($schoolId)) {
                return $schools->whereIn('sch_id', $schoolId);
            }
            $schools->where('sch_id', $schoolId);
        });
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
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
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        if ($value->isPrincipal() || $value->isSuperIntendent() || $value->isPrivateTeacher() || $value->isAssistant()) {
            return $query->bySchool();
        }
        if ($value->isTeacher()) {
            $query->bySchool()->byTeacherId($value->getKey());
        }
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isSuperIntendent()) {
            return in_array($this->school_id, $user->getSchoolIds());
        }
        if ($user->isPrincipal() || $user->isPrivateTeacher() || $user->isAssistant()) {
            return ($user->getSchool()->getKey() == $this->school_id);
        }
        if ($user->isTeacher()) {
            return $this->teachers()->where('usr_id', $user->getKey())->exists();
        }
        return FALSE;
    }

    public function updateAverageRates($data)
    {
        $this->average()->updateOrCreate([
           'homeroom_id' => $this->getKey()
        ],[
           'pass_rate'              => $data['passRate'],
           'completed_progress'     => $data['completed'],
           'inprogress_progress'    => $data['inProgress'],
           'average_grade'          => $data['averageGrade']
        ]);
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNull('archived_at');
    }

    public function archive()
    {
        $this->archived_at = Carbon::now();
        return $this->save();
    }
}
