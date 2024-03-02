<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Gift extends Model
{
    protected $table = 'gifts';

    public $timestamps = true;

    const TYPE_COURSE       = 'course';
    const TYPE_SUBSCRIPTION = 'subscription';

    const STATUS_PENDING  = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'usr_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'usr_id');
    }

    public function getUser()
    {
        $user = $this->from_user_id == Auth::id() ? $this->toUser() : $this->fromUser();
        return $user->first();
    }

    public function product()
    {
        if ($this->isCourse()) {
            return $this->belongsTo(UnassignedItem::class, 'product_id', 'id');
        }
        if ($this->isSubscription()) {
            return $this->belongsTo(UserSubscription::class, 'product_id', 'id');
        }
        return null;
    }

    public function connection()
    {
        return $this->belongsTo(UserConnection::class, 'connection_id', 'id');
    }

    public function getOwnerName()
    {
        if ($this->to_user_id) {
            return $this->toUser()->first()->getFullName();
        }
        if ($this->connection_id) {
            return $this->connection()->first()->getFullName();
        }
        return null;
    }

    public function accept(User $user = null)
    {
        $this->status = self::STATUS_ACCEPTED;

        if ($user) {
            $this->to_user_id = $user->getKey();
        }

        if ($this->to_user_id) {
            $product = $this->product()->first();

            $product->owner_id = $this->to_user_id;
            $product->save();
        }

        $this->save();
    }

    public function decline()
    {
        $this->status = self::STATUS_DECLINED;
        $this->save();

        $this->returnProduct();
    }

    public function delete()
    {
        if ($this->isPending()) {
            $this->returnProduct();
        }
        return parent::delete();
    }

    public function returnProduct()
    {
        $product = $this->product()->first();

        if ($this->isCourse()) {
            $existingItem = UnassignedItem::where('item_id', $product->item_id)
                ->where('user_id', $product->user_id)
                ->where('owner_id', $product->user_id)
                ->first();

            if ($existingItem) {
                $existingItem->changeQuantity($product->quantity);
                return $product->delete();
            }
        }

        $product->owner_id = $this->from_user_id;
        return $product->save();
    }

    public function isAccepted()
    {
        return ($this->status == self::STATUS_ACCEPTED);
    }

    public function isDeclined()
    {
        return ($this->status == self::STATUS_DECLINED);
    }

    public function isPending()
    {
        return ($this->status == self::STATUS_PENDING);
    }

    public function isWaiting()
    {
        return ($this->isPending() && Auth::id() == $this->to_user_id);
    }

    public function isCourse()
    {
        return ($this->product_type == self::TYPE_COURSE);
    }

    public function isSubscription()
    {
        return ($this->product_type == self::TYPE_SUBSCRIPTION);
    }
}
