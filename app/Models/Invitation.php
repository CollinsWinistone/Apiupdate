<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Invitation extends Model
{
    use InvitationSorter;
    use InvitationFilters;
    use PaginatorTrait;

    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;
    const STATUS_EXPIRED = 3;

    protected $table = 'invitations';

    protected $primaryKey = 'inv_id';

    protected $guarded = [];

    public $timestamps = false;

    public function scopeBySenderId(Builder $query, $userId)
    {
        return $query->where('inv_from_usr_id', $userId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver()
    {
        return $this->belongsTo(
            User::class,
            'inv_usr_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'inv_from_usr_id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(
            ClassRoom::class,
            'inv_crm_id',
            'crm_id'
        );
    }

    public function getHash()
    {
        return Hash::make(
            $this->getKey()
            . $this->inv_frd_email
            . $this->inv_from_usr_id
            . $this->inv_crm_id
        );
    }
}
