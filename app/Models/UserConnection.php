<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserConnection extends Model
{
    use PaginatorTrait, FilterTrait, SortTrait, AvatarTrait;

    protected $table = 'user_connections';

    protected $_defaultAvatar = 'https://bzabc.s3.ap-southeast-1.amazonaws.com/default-avatars/user.png';

    protected $primaryKey = 'id';

    protected $guarded = [];

    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;

    public $timestamps = true;

    public function gifts()
    {
        return $this->hasMany(Gift::class, 'connection_id', 'id');
    }

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

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        $userId = $value->getKey();

        return $query->where(function($query) use ($userId) {
            $query->where('from_user_id', $userId)->orWhere(function($query) use ($userId) {
                $query->where('to_user_id', $userId)->where('status', '!=', self::STATUS_DECLINED);
            });
        });
    }

    public function accept(User $user = null)
    {
        $this->status = self::STATUS_ACCEPTED;

        if ($user) {
            $this->to_user_id   = $user->getKey();
            $this->first_name   = null;
            $this->last_name    = null;
            $this->email        = null;
        }

        $this->save();

        foreach ($this->gifts()->getResults() as $gift) {
            $gift->accept($user);
        }
    }

    public function decline()
    {
        $this->status = self::STATUS_DECLINED;
        $this->save();

        foreach ($this->gifts()->getResults() as $gift) {
            if ($gift->isPending()) {
                $gift->decline();
            }
        }
    }

    public function delete()
    {
        foreach ($this->gifts()->getResults() as $gift) {
            if ($gift->isPending()) {
                $gift->decline();
            }
        }
        return parent::delete();
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

    public function getHash()
    {
        return Hash::make(
            $this->getKey()
            . $this->id
            . $this->from_user_id
            . $this->to_user_id
            . $this->created_at
        );
    }

    public function getFullName()
    {
        if (!$this->to_user_id) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->getUser()->getFullName();
    }

    public function isAllowedFor(User $user)
    {
        if (in_array($user->getKey(), [$this->from_user_id, $this->to_user_id])) {
            return TRUE;
        }
        return FALSE;
    }
}
