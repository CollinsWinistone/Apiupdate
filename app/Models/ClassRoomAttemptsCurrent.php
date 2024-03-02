<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClassRoomAttemptsCurrent extends Model
{
    protected $table = 'attempts_current';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    const IS_PASSED = 1;
    const IS_NOT_PASSED = 0;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'classroom_id', 'crm_id');
    }

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

    /*
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeFirstAttempts(Builder $query, $lessonId = null)
    {
         $query->where('att_next', '!=', 'done')
                ->where('attempt_no', 1);

         if ($lessonId) {
             $query->where('lesson_id', $lessonId);
         }

         return $query;
    }

    /*
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeFirstUnitAttempts(Builder $query, $unitId)
    {
        return $query->where('unit_id', $unitId)
                ->where('att_next', '!=', 'done')
                ->where('attempt_no', 1);
    }
}
