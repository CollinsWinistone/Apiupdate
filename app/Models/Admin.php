<?php
namespace App\Models;

class Admin extends User
{
    const ROLE = 'administrator';
    const ROLE_ID = 6;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new AdminObserver());
    }
}
