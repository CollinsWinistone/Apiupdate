<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $table = 'affiliates';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const TYPE_WHOLESALER   = 'wholesaler';
    const TYPE_DISTRIBUTOR  = 'distributor';
    const TYPE_RETAIL       = 'retail';

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
    public function parentAffiliate()
    {
        return $this->belongsTo(Affiliate::class, 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(AffiliateTransaction::class, 'affiliate_id', 'id');
    }

    public function getDiscount()
    {
        return AffiliateProduct::DEFAULT_DISCOUNT;
    }

    public function isWholesaler()
    {
        return (self::TYPE_WHOLESALER == $this->type);
    }

    public function isDistributor()
    {
        return (self::TYPE_DISTRIBUTOR == $this->type);
    }

    public function isRetail()
    {
        return (self::TYPE_RETAIL == $this->type);
    }
}
