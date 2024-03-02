<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;


class MessageGroup extends Model
{
    use PaginatorTrait, GroupFilter, GroupSorter;

    protected $table = 'messages_groups';

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'recipient_ids' => 'array',
        'roles'         => 'array'
    ];

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

    public function scopeHasChats(Builder $query, $userId = null, $unreadOnly = false)
    {
        if ($unreadOnly) {
            return $query->whereHas('messageUsers', function ($query) use ($userId) {
                $query->where('messages_users.user_id', $userId);
                $query->whereNull('messages_users.read_at');
            });
        }
        return $query->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhereHas('messageUsers', function ($query) use ($userId) {
                $query->where('messages_users.user_id', $userId);
            });
        });
    }

    public function isAllowedFor(User $user)
    {
        return ($this->user_id == $user->getKey());
    }

    public function newMessages()
    {
        return $this->chatMessages()
                ->where('messages_users.user_id', Auth::id())
                ->whereNull('read_at')
                ->count();
    }

    public function chatMessages()
    {
        return $this->belongsToMany(
            Message::class,
            'messages_users',
            'group_id',
            'message_id',
            'id',
            'id'
        );
    }

    public function messages()
    {
        return $this->hasMany(
            Message::class,
            'group_id',
            'id'
        );
    }

    public function messageUsers()
    {
        return $this->hasMany(
            MessageUser::class,
            'group_id',
            'id'
        );
    }

    public function lastMessage()
    {
        return $this->hasOne(
            Message::class,
            'group_id',
            'id'
        )->orderBy('id', 'DESC')->limit(1);
    }

    public function updateLastActivity()
    {
        $this->last_activity = Carbon::now()->toDateTimeString();
        $this->save();
    }
}
