<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AffiliateProduct extends Model
{
    protected $table = 'affiliate_products';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const DEFAULT_DISCOUNT      = 15;
    const WHOLESALER_PERCENT    = 30;
    const DISTRIBUTOR_PERCENT   = 20;
    const RETAIL_PERCENT        = 0;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id', 'id');
    }
}
