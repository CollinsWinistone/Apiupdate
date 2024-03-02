<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShoppingCartItem extends Model
{
    use PaginatorTrait, ShoppingCartFilter, ShoppingCartSorter;

    protected $table = 'shopping_cart';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    const IS_INVOICE = 1;
    const IS_NOT_INVOICE = 0;

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(new ShoppingCartItemObserver());

    }

    /**
     * @return bool
     */
    public function isInvoice()
    {
        return $this->is_invoice == self::IS_INVOICE;
    }

    public function isDigital()
    {
        return $this->storeItem ? $this->storeItem->isDigital() : false;
    }

    public function isBoughtByUser($user)
    {
        return $this->storeItem ? $this->storeItem->isBoughtByUser($user) : false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id', 'id');
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
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'usr_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeByInvoices($query)
    {
        return $query
            ->where('shopping_cart.is_invoice', self::IS_INVOICE);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeByNotInvoices($query)
    {
        return $query
            ->where('shopping_cart.is_invoice', self::IS_NOT_INVOICE);
    }

    /**
     * @param $query
     * @param $storeItemIds
     * @return mixed
     */
    public function scopeByStoreItems($query, $storeItemIds)
    {
        return $query->whereIn('shopping_cart.store_item_id', $storeItemIds);
    }

    public function openInvoices()
    {
        return $this->hasMany(
            OpenInvoice::class,
            'shopping_cart_item_id'
        );
    }

    public function isSchoolStore()
    {
        return $this->storeItem->isSchoolStore();
    }

    public function isParentStore()
    {
        return $this->storeItem->isParentStore();
    }

    public function getPrice()
    {
        return ($this->storeItem->getDiscountPrice() * $this->quantity);
    }

    public function getAffiliateDiscount()
    {
        return ($this->storeItem->getAffiliateDiscount() * $this->quantity);
    }

    public function getInvoiceNumber()
    {
        if ($this->invoice_no) {
            return $this->invoice_no;
        }
        return self::createInvoiceNumber();
    }

    public static function createInvoiceNumber()
    {
        $date = Carbon::now();

        return $date->year . '-' . str_pad(rand(100, 1000000), 6, '0') . $date->month . $date->day;
    }
}
