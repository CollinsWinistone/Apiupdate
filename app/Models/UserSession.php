<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class UserSession extends Model
{
    use PaginatorTrait, FilterTrait, SortTrait;

    protected $table = 'user_sessions';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'usr_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'usr_id');
    }

    public function getHash()
    {
        return Str::random(64);
    }

    public function isValid(User $user)
    {
        if (Carbon::now()->toDateTimeString() > $this->expired_at) {
            return false;
        }
        return ($user->getKey() == $this->student_id);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->hash = $model->getHash();
            $model->expired_at = Carbon::now()->addDay()->toDateTimeString();
        });
    }
}
