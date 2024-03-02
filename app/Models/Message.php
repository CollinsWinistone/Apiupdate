<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Message extends Model
{
    use PaginatorTrait, MessageFilter, SortTrait;

    protected $table = 'messages';

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'messages_users',
            'message_id',
            'user_id',
            'id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function messageUsers()
    {
        return $this->hasMany(
            MessageUser::class,
            'message_id',
            'id'
        );
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
    public function group()
    {
        return $this->belongsTo(MessageGroup::class, 'group_id', 'id');
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    /*public function scopeByUserId(Builder $query, $userId = null)
    {
        return $query->where('user_id', $userId);
    }*/

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeByRecipient(Builder $query, $userId = null)
    {
        return $query->whereHas('messageUsers', function ($users) use ($userId) {
            $users->where('user_id', $userId);
        });
    }

    public function scopeByUserId(Builder $query, $userId = null)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhereHas('messageUsers', function ($users) use ($userId) {
                $users->where('user_id', $userId);
            });
        });
    }

    public function scopeByRecipientId(Builder $query, $userId = null)
    {
        return $query->where(function ($query) use ($userId) {
            $query->whereIn('user_id', [$userId, Auth::id()])->whereIn('recipient_id', [$userId, Auth::id()]);
        });
    }

    public function scopeByGroupId(Builder $query, $groupId = null)
    {
        return $query->where('group_id', $groupId);
    }

    public function isAllowedFor(User $user)
    {
        if ($user->getKey() == $this->user_id) {
            return true;
        }
        return $this->isRecipient($user);
    }

    public function isRecipient(User $user)
    {
        return $this->messageUsers()->byUserId($user->getKey())->exists();
    }

    public function isOwner(User $user)
    {
        return $this->user_id == $user->getKey();
    }

    public function isPrivate()
    {
        return (bool) $this->recipient_id;
    }

    public function recipientName()
    {
        if (!$this->isPrivate()) {
            return $this->group()->first()->name ?? '-';
        }
        $user = $this->users()->first();
        return $user ? $user->getFullName() : '-';
    }

    public function read(User $user)
    {
        $messageUsers = $this->messageUsers()->byUser($user, false)->get();

        foreach ($messageUsers as $messageUser) {
            $messageUser->read();
        }
    }
}
