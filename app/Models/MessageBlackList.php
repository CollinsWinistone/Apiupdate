<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MessageBlackList extends Model
{
    protected $table = 'messages_black_list';

    protected $primaryKey = 'id';

    public $timestamps = true;

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
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeByUserId(Builder $query, $userId = null)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeByUserIds(Builder $query, $userIds = [])
    {
        return $query->whereIn('user_id', $userIds);
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeByChat(Builder $query, $chatIdName)
    {
        return $query->whereIn('chat', $chatIdName);
    }
}
