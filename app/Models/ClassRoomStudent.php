<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ClassRoomStudent extends Model
{
    protected $table = 'classroom_student';

    protected $primaryKey = 'enr_id';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(Student::class, 'enr_user', 'usr_id');
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'enr_classroom', 'crm_id')->withoutGlobalScopes();
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'enr_user', 'usr_id')->withoutGlobalScopes();
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id', 'id');
    }

    public function scopeByStudentId(Builder $query, $id)
    {
        return $query->where('enr_user', $id);
    }

    public function isActive()
    {
        if (!$this->subscription) {
            return true;
        }
        return $this->subscription->is_active;
    }

    public function isParents()
    {
        return (bool) $this->user_subscription_id;
    }

    public function updateLastActive()
    {
        $now = Carbon::now();

        if (!$this->last_active || $now->diffInMinutes($this->last_active) > 1) {
            $this->last_active = $now->toDateTimeString();
            return $this->save();
        }
    }
}
