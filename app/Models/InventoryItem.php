<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory';

    const IN_STOCK = 'in_stock';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = true;

    public function scopeInStock($query)
    {
        return $query
            ->whereNull('owner_id')
            ->whereNull('transaction_id')
            ->whereNull('school_id')
            ->orderByRaw("CASE WHEN status = 'in_stock' THEN 2 WHEN status = 'pre_order' THEN 1 ELSE 0 END DESC");
    }

    public function assignTo($studentId, $classRoomId)
    {
        $this->student_id   = $studentId;
        $this->classroom_id = $classRoomId;
        $this->save();
    }

    public function refund()
    {
        $unassignedItem = UnassignedItem::where('item_id', $this->store_item_id)
            ->where('user_id', $this->owner_id)
            ->where('owner_id', $this->owner_id)
            ->first();

        if ($unassignedItem) {
            $unassignedItem->changeQuantity(1);
            $unassignedItem->save();
        } else {
            $unassignedItem = new UnassignedItem([
                'item_id'   => $this->store_item_id,
                'owner_id'  => $this->owner_id,
                'user_id'   => $this->owner_id,
                'quantity'  => 1
            ]);
            $unassignedItem->save();
        }

        $this->student_id       = null;
        $this->classroom_id     = null;
        $this->is_activated     = 0;
        $this->save();

        return true;
    }
}
