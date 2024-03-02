<?php
namespace App\Models;

class SuperIntendent extends User
{
    const ROLE = 'superintendent';
    const ROLE_ID = 1;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });
    }
}
