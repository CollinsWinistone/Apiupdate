<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Assistant extends User
{
    const ROLE = 'assistant';
    const ROLE_ID = 13;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new AssistantObserver());
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByUser(Builder $query, $value)
    {
        return $query->bySchool();
    }

    public function isAllowedFor(User $user)
    {
        if ($user->isPrivateTeacher()) {
            return ($user->getSchool()->getKey() == $this->getSchool()->getKey());
        }
        return FALSE;
    }
}
