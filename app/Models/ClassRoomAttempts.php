<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomAttempts extends Model
{
    use PaginatorTrait, SortTrait, FilterTrait;

    protected $table = 'attempts';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    const IS_PASSED = 1;
    const IS_NOT_PASSED = 0;

    public function setMetadataAttribute($metadata)
    {
        $this->attributes['metadata'] = is_array($metadata) ? json_encode($metadata) : $metadata;
    }

    /**
     * @return bool
     */
    public function isPassed()
    {
        return $this->pass == 1;
    }

    public function scopeByUser($query, User $user)
    {
        if ($user->isTeacher()) {
            $query->join('classroom_teacher', function ($join) use ($user) {
                $join->on('classroom_teacher.classroom', '=', 'attempts.classroom_id')
                    ->where('classroom_teacher.teacher', $user->getKey());
            });
        }
        return $query;
    }
}
