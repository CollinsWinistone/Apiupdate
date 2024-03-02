<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class School extends Model
{
    use PaginatorTrait, SchoolFilter, SchoolSorter;

    protected $table = 'schools';

    protected $primaryKey = 'sch_id';

    public $timestamps = false;

    const DEFAULT_ID = 1;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'users_school',
            'school_id',
            'user_id',
            'sch_id',
            'usr_id'
        );
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'users_school',
            'school_id',
            'user_id',
            'sch_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function principals()
    {
        return $this->belongsToMany(
            Principal::class,
            'users_school',
            'school_id',
            'user_id',
            'sch_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'users_school',
            'school_id',
            'user_id',
            'sch_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(
            Admin::class,
            'users_school',
            'school_id',
            'user_id',
            'sch_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classrooms()
    {
        return $this->hasMany(ClassRoom::class, 'crm_school', 'sch_id')->whereNull('archived_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function homerooms()
    {
        return $this->hasMany(HomeRoom::class, 'school_id', 'sch_id')->whereNull('archived_at');
    }

    public function getAvatar()
    {
        return $this->sch_image ? $this->sch_image : 'https://bzabc.s3.ap-southeast-1.amazonaws.com/default-avatars/school.png';
    }

    public function scopeByUser(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isSuperIntendent()) {
            return $query->whereIn('sch_id', $user->getSchoolIds());
        }
        return $query->where('sch_id', $user->getSchoolId());
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isAdmin()) {
            return TRUE;
        }
        if ($user->isSuperIntendent()) {
            return in_array($this->sch_id, $user->getSchoolIds());
        }
        return ($user->getSchool()->getKey() == $this->sch_id);
    }
}
