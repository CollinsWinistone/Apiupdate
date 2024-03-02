<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Survey extends Model
{
    use SurveyFilter, PaginatorTrait, SurveySorter;

    const NOT_PUBLISHED = 'not_published';
    const PUBLISHED     = 'published';

    protected $table = 'surveys';

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'survey_teachers',
            'survey_id',
            'teacher_id',
            'id',
            'usr_id'
        )->withoutGlobalScopes();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answerSets()
    {
        return $this->hasMany(SurveyStudent::class, 'survey_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class, 'survey_id', 'id')->ordered();
    }

    public function isAssignedTo($id)
    {
        if (is_object($id)) {
            $id = $id->usr_id;
        }
        if ($this->user_id == $id) {
            return true;
        }
        return $this->teachers()->where('usr_id', $id)->exists();
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }

        if ($user->isPrincipal() || $user->isSuperIntendent() || $user->isPrivateTeacher()) {
            return ($this->user_id == $user->usr_id);
        }

        if ($user->isTeacher()) {
            return $this->isAssignedTo($user);
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
        if ($value->isTeacher()) {
            return $query->whereHas('teachers', function ($teachers) use ($value) {
                $teachers->where('usr_id', $value->usr_id);
            });
        }
        if ($value->isPrincipal() || $value->isPrivateTeacher()) {
            return $query->where('user_id', $value->usr_id);
        }
    }
}
