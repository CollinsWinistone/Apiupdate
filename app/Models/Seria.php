<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seria extends Model
{
    use PaginatorTrait, SortTrait, FilterTrait;

    protected $table = 'series';

    protected $primaryKey = 'series_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'crs_series_id', 'series_id')
                ->where('is_active', 1)
                ->where('is_demo', 0)
                ->where('is_exam', 0)
                ->where('is_draft', 0)
                ->orderBy('crs_level', 'ASC');
    }
}
