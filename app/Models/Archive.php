<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    use PaginatorTrait, SortTrait, FilterTrait;

    protected $table = 'archive';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        if ($this->entity_type == 'student') {
            return $this->belongsTo(Student::class, 'entity_id', 'usr_id');
        }
        return $this->belongsTo(ClassRoom::class, 'entity_id', 'crm_id');
    }
}
