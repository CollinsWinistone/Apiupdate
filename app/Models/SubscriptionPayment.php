<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Hash;

class SubscriptionPayment extends Model
{
    use PaginatorTrait;

    protected $table = 'subscription_payments';

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function getNumber()
    {
        return $this->userSubscription->getNumber() . '-' . $this->id;
    }

    public function getHash()
    {
        return Hash::make(
            chr($this->user_id % 255) . $this->id . $this->user_id . $this->subscription_id . $this->user_subscription_id . chr($this->id % 255)
        );
    }
}
