<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class TransactionItem extends Model
{
    protected $table = 'store_transaction_items';

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'item_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class,'transaction_id');
    }

    public function openInvoices()
    {
        return $this->hasMany(
            OpenInvoice::class,
            'store_item_id',
            'item_id'
        );
    }

    public function scopeByInvoice($query)
    {
        return $query->where('is_invoice', 1);
    }

    public function scopeNotByInvoice($query)
    {
        return $query->where('is_invoice', 0);
    }

    public function scopeForCredits($query)
    {
        return $query->where('is_invoice', 0)->whereHas('item', function (Builder $query) {
            $query->whereNotNull('course_id');
        });
    }

    public function getThumbnail()
    {
        return $this->item ? $this->item->getThumbnail() : '';
    }

    public function isDigital()
    {
        return $this->item ? $this->item->isDigital() : false;
    }

    public function getPdfUrl()
    {
        return $this->item ? $this->item->pdf_path : null;
    }

    public function getDownloadUrl()
    {
        return $this->isDigital() ? url("/1.0/store/download-pdf/{$this->getKey()}/{$this->getHash()}", [], true) : NULL;
    }

    public function getHash()
    {
        return Hash::make(
            chr($this->id % 255) . $this->item_id . $this->transaction_id . $this->created_at . $this->transaction->user_id . chr($this->id % 255)
        );
    }
}
