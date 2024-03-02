<?php
namespace App\Models;

class SuperAdmin extends User
{
    const ROLE = 'superadministrator';
    const ROLE_ID = 7;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });
    }
}
