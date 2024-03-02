<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTransaction extends Model
{
    protected $table = 'affiliate_transactions';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const TYPE_STORE        = 'store';
    const TYPE_SUBSCRIPTION = 'subscription';

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
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }
}
