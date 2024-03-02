<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Builder;

class PrivateTeacher extends User
{
    const ROLE = 'privateteacher';
    const ROLE_ID = 12;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new PrivateTeacherObserver());
    }
}
