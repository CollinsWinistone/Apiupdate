<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Transaction extends Model
{
    use PaginatorTrait, TransactionSorter, TransactionFilter;

    protected $table = 'store_transactions';

    const PAID      = 'paid';
    const DUE       = 'due';
    const DECLINED  = 'declined';

    /**
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    protected $dates = [
        'created_at'
    ];

    public function getMetaAttribute($value)
    {
        return json_decode($value);
    }

    public function setAuthorizedAtAttribute($value){
        $this->attributes['authorized_at']  = $value;
        $this->attributes['status']         = $value ? 'paid' : 'due';
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(
            TransactionItem::class,
            'transaction_id'
        );
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
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
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'sch_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'item_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingCountry()
    {
        return $this->belongsTo(Country::class, 'shipping_country', 'cnt_code');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billingCountry()
    {
        return $this->belongsTo(Country::class, 'billing_country', 'cnt_code');
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorized_at !== null;
    }

    /**
     * @return bool
     */
    public function isDeclined()
    {
        return $this->declined_at !== null;
    }


    /**
     * @return bool
     */
    public function isDigital()
    {
        foreach ($this->items as $item) {
            if (!$item->isDigital()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function distributor()
    {
        return $this->hasOne(
            Distributor::class,
            'id',
            'distributor_id'
        );
    }

    /**
     * @param $query
     * @param $storeItemIds
     * @return mixed
     */
    public function scopeDue($query)
    {
        return $query->whereNull('authorized_at');
    }

    /**
     * @param $query
     * @param $storeItemIds
     * @return mixed
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('authorized_at');
    }

    /**
     * @param Transaction $transaction
     */
    public function authorize()
    {
        $this->status           = self::PAID;
        $this->authorized_at    = now();
        $this->save();
    }

    public function due($message = '')
    {
        $this->authorized_at = null;
        $this->status        = self::DUE;
        $this->notes         = $message;
        $this->save();
    }

    public function decline($message)
    {
        $this->declined_at  = now();
        $this->status       = self::DECLINED;
        $this->notes        = $message ?? '';
        $this->save();
    }

    public function getHash()
    {
        return Hash::make(
            $this->id . $this->invoice_no . $this->created_at
        );
    }

    public function getInvoiceUrl()
    {
        return url("/1.0/checkout/invoice-pdf/{$this->invoice_no}/{$this->getHash()}", [], true);;
    }
}
