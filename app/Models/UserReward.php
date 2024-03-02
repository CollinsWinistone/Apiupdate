<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserReward extends Model
{
    use PaginatorTrait, FilterTrait, SortTrait;

    protected $table = 'user_rewards';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $timestamps = true;

    const SCHOOLS = 'schools';
    const PARENTS = 'parents';

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
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(UserRewardTransaction::class, 'transaction_id', 'id');
    }

    static public function getType(User $user)
    {
        return $user->isParent() ? self::PARENTS : self::SCHOOLS;
    }

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, User $user)
    {
        if ($user->isTeacher()) {
            return $query->where('teacher_id', $user->getKey());
        }
        return $query->whereNull('teacher_id')->where('student_id', $user->getKey());
    }

    public function notActual()
    {
        $this->is_actual = 0;
        return $this->save();
    }
}
