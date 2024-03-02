<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserSubscriptionStudent extends Model
{
    protected $table = 'user_subscription_students';

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
    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'classroom_id', 'crm_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'crs_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seria()
    {
        return $this->belongsTo(Seria::class, 'series_id', 'series_id');
    }

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
        return $this->belongsTo(Student::class, 'student_id', 'usr_id')->withoutGlobalScopes();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userSubscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id', 'id');
    }

    public function learningPaths()
    {
        return $this->hasMany(LearningPath::class, 'series_id', 'series_id')
                ->where('student_id', $this->student_id);
    }

    public function unsubscribe(User $student = null)
    {
        if (!$student) {
            $student = $this->student;
        }

        $this->classroom->removeStudent($student);

        $this->userSubscription->assigned_courses = ($this->userSubscription->students()->count() - 1);
        $this->userSubscription->save();

        if ($this->series_id) {
            foreach ($this->learningPaths as $learningPath) {
                if (!$learningPath->classroom_id || $this->classroom_id == $learningPath->classroom_id) {
                    continue;
                }
                $learningPath->classroom->removeStudent($student);
                self::where('series_id', $this->series_id)
                        ->where('user_subscription_id', $this->user_subscription_id)
                        ->where('student_id', $student->getKey())
                        ->where('id', '!=', $this->getKey())
                        ->delete();
            }
            $this->learningPaths()->delete();
        }

        $this->delete();
    }

    public function reEnrollDate()
    {
        if (!$this->subscription->is_default) {
            return null;
        }
        return $this->created_at->addWeek()->toDateTimeString();
    }

    public function canReEnroll()
    {
        $reEnrollDate = $this->reEnrollDate();
        if (!$reEnrollDate) {
            return true;
        }
        return (Carbon::now()->toDateTimeString() > $reEnrollDate);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model) {
            $subscription = $model->userSubscription;
            if ($subscription) {
                $subscription->assigned_courses = $subscription->students()->count() - 1;
                $subscription->save();
            }
        });
    }
}
