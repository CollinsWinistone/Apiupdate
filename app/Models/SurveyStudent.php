<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SurveyStudent extends Model
{
    use SurveyStudentFilter, PaginatorTrait, SurveyStudentSorter;

    const DRAFT = 'draft';
    const COMPLETED = 'completed';

    protected $table = 'survey_students';

    public $timestamps = true;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'usr_id')->withoutGlobalScopes();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'usr_id')->withoutGlobalScopes();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function homeroom()
    {
        return $this->belongsTo(HomeRoom::class, 'homeroom_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_student_id', 'id');
    }

    /**
     * @param Builder $query
     * @param int $value
     * @return Builder
     */
    public function scopeByTeacherId(Builder $query, $value)
    {
        return $query->where('teacher_id', $value);
    }

    /**
     * @param Builder $query
     * @param int $value
     * @return Builder
     */
    public function scopeByStudentId(Builder $query, $value)
    {
        return $query->where('student_id', $value);
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        if ($value->isTeacher()) {
            return $query->where('teacher_id', $value->usr_id);
        }
        if (!$value->isPrivateTeacher()) {
            return $query->completed();
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', self::COMPLETED);
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isPrincipal() || $user->isSuperIntendent() || $user->isPrivateTeacher()) {
            return ($this->survey()->getResults()->user_id == $user->usr_id);
        }
        if ($user->isTeacher()) {
            return $this->teacher_id == $user->usr_id;
        }
        return FALSE;
    }
}
