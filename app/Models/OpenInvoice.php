<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OpenInvoice extends Model
{
    protected $table = 'open_invoices';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function shoppingCartItem()
    {
        return $this->belongsTo(
            ShoppingCartItem::class,
            'shopping_cart_item_id'
        );
    }

    public function storeItem()
    {
        return $this->belongsTo(
            StoreItem::class,
            'store_item_id'
        );
    }

    protected function classroom()
    {
        return $this->belongsTo(
            ClassRoom::class,
            'classroom_id',
            'crm_id'
        );
    }

    protected function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id',
            'usr_id'
        )->withoutGlobalScopes();
    }

    public function scopeInTransaction($query)
    {
        return $query->whereNotNull('transaction_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
