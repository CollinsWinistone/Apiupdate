<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserSubscription extends Model
{
    use UserSubscriptionFilter, PaginatorTrait, UserSubscriptionSorter;

    protected $table = 'user_subscriptions';

    public $timestamps = true;

    const TYPE_CREDIT_CARD  = 'creditcard';
    const TYPE_APPLE_PAY    = 'applepay';
    const TYPE_GOOGLE_PAY   = 'googlepay';

    const CLOSED        = 0;
    const ACTIVE        = 1;
    const LAST_PERIOD   = 2;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    protected $dates = [
        'paid_at', 'created_at', 'updated_at', 'expired_at', 'trial_expired_at'
    ];

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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gifts()
    {
        return $this->hasMany(Gift::class, 'product_id', 'id')->where('product_type', Gift::TYPE_SUBSCRIPTION);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'user_subscription_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students()
    {
        return $this->hasMany(UserSubscriptionStudent::class, 'user_subscription_id', 'id');
    }

    public function studentsCount($excludeId = null)
    {
        $query = $this->students();
        if ($excludeId) {
            $query->where('student_id', '!=', $excludeId);
        }
        return $query->distinct('student_id')->count('student_id');
    }

    public function coursesCount($studentId = null)
    {
        $query = $this->students();
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        return $query->count(DB::raw('DISTINCT IFNULL(series_id, id)')); + $this->coursesSeries();
    }

    public function setExpirationDates($trialDays = 0)
    {
        $date = Carbon::now()->addDay();

        if ($trialDays) {
            $date->addDays($trialDays);
            $this->trial_expired_at = $date->toDateTimeString();
        }

        if ($this->period == 'year') {
            $date->addYear();
        } else {
            $date->addMonth();
        }

        $this->expired_at = $date->toDateTimeString();

        return $this;
    }

    public function getName()
    {
        return $this->subscription()->first()->title;
    }

    public function getNumber()
    {
        $date = Carbon::parse($this->created_at);
        return $date->year . '-' . $this->id . $date->month . $date->day;
    }

    public function getHash()
    {
        return Hash::make(
            $this->id. $this->getNumber() . $this->created_at
        );
    }

    public function getOwnerName()
    {
        if ($this->owner_id) {
            return $this->owner()->first()->getFullName();
        }

        $gift = $this->gifts()->first();

        return $gift ? $gift->getOwnerName() : '-';
    }

    public function isGift()
    {
        return ($this->owner_id == Auth::id() && ($this->user_id != $this->owner_id));
    }

    public function isTrial()
    {
        return ($this->trial_expired_at && Carbon::parse($this->trial_expired_at) > Carbon::now());
    }

    public function gift($userId = null)
    {
        $this->owner_id = $userId;
        return $this->save();
    }

    public function stopRecurringPayments()
    {
        $this->is_active = self::LAST_PERIOD;
        return $this->save();
    }

    public function cancel()
    {
        $this->is_active = self::CLOSED;
        $this->save();

        foreach ($this->students()->getResults() as $studentSubscription) {
            $studentSubscription->unsubscribe();
        }
        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', '>', self::CLOSED);
    }
}
