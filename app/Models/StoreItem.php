<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreItem extends Model
{
    use PaginatorTrait, StoreItemFilter, StoreItemSorter, SoftDeletes;

    protected $table = 'store_items';

    const TYPE_SCHOOL = 'school';
    const TYPE_PARENT = 'parent';

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'currency_prices' => 'array'
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(new StoreItemObserver());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'crs_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function affiliateDiscount()
    {
        return $this->hasOne(AffiliateProduct::class, 'store_item_id', 'id')->where('exired_at', '>=', Carbon::now()->toDateString());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function target()
    {
        return $this->belongsTo(StoreTarget::class, 'target_id', 'trg_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function digitalItem()
    {
        return $this->belongsTo(StoreItem::class, 'digital_item_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(StoreSubject::class, 'subject_id', 'sbj_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryItems()
    {
        return $this->hasMany(
            InventoryItem::class,
            'store_item_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(
            StoreItemImage::class,
            'store_item_id'
        );
    }

    /**
     * @return Model|null|object|static
     */
    public function unassignedInventoryItem($ownerId)
    {
        return $this->inventoryItems()
            ->whereNull('student_id')
            ->where('owner_id', $ownerId)
            ->where('store_item_id', $this->id)
            ->first();
    }

    /**
     * @param Builder $query
     * @param string $type
     */
    public function scopeAvailable(Builder $query, $type = 'school')
    {
        $query->whereHas('inventoryItems', function (Builder $query) {
            $query->whereNull('owner_id');
        })->where('type', $type);
    }

    public function getAffiliateDiscount()
    {
        if ($this->affiliate_discount) {
            return round(($this->getDiscountPrice() / 100) * $this->affiliate_discount, 2);
        }
        return 0;
    }

    public function getPrice()
    {
        if (Currency::getCurrency() != $this->currency) {
            $prices = $this->getPrices();
            return $prices[Currency::getCurrency()] ?? 0;
        }
        return (float) $this->price;
    }

    public function getCurrency()
    {
        return Currency::getCurrency();
    }

    public function getPrices()
    {
        $result = [];
        $prices = $this->currency_prices;

        foreach (Currency::getList() as $currency) {
            $result[$currency] = (float) (empty($prices[$currency]) ? Currency::convert($this->price, $currency) : number_format($prices[$currency], 2));
        }
        return $result;
    }

    public function getDiscountPrice()
    {
        $price = $this->getPrice();

        if (!is_numeric($this->discount) || !(float) $this->discount) {
            return (float) $price;
        }
        return (float) ($price * ((100 - (float) $this->discount) / 100));
    }

    public function getThumbnail()
    {
        return str_replace(['http://', 'gb-store.s3-ap-southeast-1.amazonaws.com'], ['//', 'd3ewsge0d628ka.cloudfront.net'], $this->thumbnail);
    }

    public function isSchoolStore()
    {
        return ($this->type == self::TYPE_SCHOOL);
    }

    public function isParentStore()
    {
        return ($this->type == self::TYPE_PARENT);
    }

    public function isDigital()
    {
        return ($this->is_digital_only && $this->pdf_path);
    }

    public function isBzPoints()
    {
        return ($this->category_id == StoreCategory::POINTS);
    }

    public function isBoughtByUser($userId = null)
    {
        if ($userId instanceof Model) {
            $userId = $userId->getKey();
        }
        if (!$userId) {
            $userId = Auth::id();
        }
        if (!$userId) {
            return false;
        }

        return $this->transactionItems()
                ->join('store_transactions', 'store_transactions.id', '=', 'store_transaction_items.transaction_id')
                ->where('store_transactions.user_id', $userId)
                ->whereNull('store_transactions.declined_at')
                ->exists();
    }
}
