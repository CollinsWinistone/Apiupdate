<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use PaginatorTrait, CoursesSorter, CoursesFilter;

    protected $table = 'courses';

    protected $primaryKey = 'crs_id';

    public $timestamps = false;

    const TYPE_DEMO = 1;
    const TYPE_SCHOOLS = 2;
    const TYPE_PARENTS = 3;
    const TYPE_BOTH = 4;
    const TYPE_PRIVATE = 5;

    const IS_DEMO = 1;
    const IS_NOT_DEMO = 0;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function lessons()
    {
        return $this->hasManyThrough(
            Lesson::class,
            Unit::class,
            'unt_course',
            'lsn_unit'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(
            Publisher::class,
            'crs_publisher_id',
            'id'
        );
    }

    public function classrooms()
    {
        return $this->hasMany(
            ClassRoom::class,
            'crm_course',
            'crs_id'
        );
    }

    public function units()
    {
        return $this->hasMany(
            Unit::class,
            'unt_course',
            'crs_id'
        );
    }

    public function autoCreateTask()
    {
        return $this->hasOne(ClassRoomAutoTask::class,'course_id','crs_id');
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeBySchool(Builder $query)
    {
        if (Auth::user()->isSuperIntendent()) {
            $query->whereIn('crs_school_id', Auth::user()->getSchoolIds());
        }
        return $query->where('crs_school_id', Auth::user()->getSchoolId());
    }

    public function schoolStoreItems()
    {
        return $this->hasMany(
            StoreItem::class,
            'course_id'
        )->where('type', 'school');
    }

    public function availableSchoolStoreItems()
    {
        return $this->schoolStoreItems()->available('school');
    }

    /**
     * @param Builder $query
     * @param string $type
     * @return Builder|static
     */
    public function scopeAvailableInStore(Builder $query, $type = 'school')
    {
        if ($type === 'school') {
            return $query->has('availableSchoolStoreItems');
        }
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeNotInClassrooms(Builder $query)
    {
        return $query->doesntHave('classrooms');
    }

    /**
     * @param Builder $query
     * @return $this
     */
    public function scopeBySchoolCourses(Builder $query)
    {
        return $query->where('is_demo', '<>', self::IS_DEMO);
    }


    /**
     * @return bool
     */
    public function isDemo()
    {
        return $this->is_demo == self::IS_DEMO;
    }

    public function publicClassroom(Seria $seria = null)
    {
        return $this->hasMany(ClassRoom::class, 'crm_course','crs_id')
                ->where('crm_is_public', 1)
                ->bySeria($seria);
    }
}
