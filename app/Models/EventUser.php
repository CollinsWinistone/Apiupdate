<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EventUser extends Model
{
    use PaginatorTrait, FilterTrait, SortTrait;

    protected $table = 'events_users';

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
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

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
    public function scopeByUser(Builder $query, $user)
    {
        return $query->byUserId($user->getKey());
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeByType(Builder $query, $type)
    {
        return $query->whereHas('event', function ($query) use ($type) {
            if (is_array($type)) {
                $query->whereIn('type', $type);
            } else {
                $query->where('type', $type);
            }
        });
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->whereHas('event', function ($query) {
            $query->where('expired_at', '>=', Carbon::now()->toDateString())->orWhereNull('expired_at');
        });
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeAlerts(Builder $query)
    {
        return $query->whereHas('event', function ($query) {
            $query->where('type', Event::TYPE_ALERT);
        });
    }

    public function isRead()
    {
        return (bool) $this->read_at;
    }

    public function read()
    {
        if (!$this->read_at) {
            $this->read_at = Carbon::now()->toDateTimeString();
            return $this->save();
        }
    }
}
