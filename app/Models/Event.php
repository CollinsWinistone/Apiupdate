<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use PaginatorTrait, EventFilter, SortTrait;

    const TYPE_ALERT        = 'alert';
    const TYPE_ANNOUCEMENT  = 'announcement';
    const TYPE_ASSIGNMENT   = 'assignment';


    protected $table = 'events';

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
            'events_users',
            'event_id',
            'user_id',
            'id',
            'usr_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventUsers()
    {
        return $this->hasMany(
            EventUser::class,
            'event_id',
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
    public function scopeByRecipient(Builder $query, $userId = null)
    {
        return $query->whereHas('eventUsers', function ($users) use ($userId) {
            $users->where('user_id', $userId);
        });
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->where(function ($query) {
            $query->where('expired_at', '>=', Carbon::now()->toDateString())->orWhereNull('expired_at');
        });
    }

    public function scopeByUserId(Builder $query, $userId = null)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhereHas('eventUsers', function ($users) use ($userId) {
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

    public function scopeByType(Builder $query, $type = null)
    {
        return $query->where('type', $type);
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
        return $this->eventUsers()->byUserId($user->getKey())->exists();
    }

    public function isOwner(User $user)
    {
        return $this->user_id == $user->getKey();
    }

    public function recipientName()
    {
        return $this->group()->first()->name ?? '-';
    }

    public function read(User $user)
    {
        $eventUsers = $this->eventUsers()->byUser($user, false)->get();

        foreach ($eventUsers as $eventUser) {
            $eventUser->read();
        }
    }
}
