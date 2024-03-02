<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UnassignedItem extends Model
{
    use PaginatorTrait, UnassignedItemSorter, UnassignedItemFilter;

    protected $table = 'unassigned_items';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param $quantity
     */
    public function changeQuantity($quantity)
    {
        $this->quantity += $quantity;
        $this->save();
    }

    public function decreaseQuantity()
    {
        $this->quantity --;

        if ($this->quantity <= 0) {
            return $this->delete();
        }

        return $this->save();
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'usr_id');
    }

    public function gift($userId = null, $quantity = 1)
    {
        if ($quantity > $this->quantity) {
            $quantity = $this->quantity;
        }

        if ($this->quantity > $quantity) {
            $ownerItem = new UnassignedItem();
            $ownerItem->fill([
                'user_id'  => $this->user_id,
                'owner_id' => $this->user_id,
                'item_id'  => $this->item_id,
                'quantity' => $this->quantity - $quantity
            ]);
            $ownerItem->save();
        }

        $this->quantity = $quantity;
        $this->owner_id = $userId;
        return $this->save();
    }

    public function isGift()
    {
        return ($this->owner_id == Auth::id() && ($this->user_id != $this->owner_id));
    }

    public function getName()
    {
        return $this->item()->first()->title;
    }
}
