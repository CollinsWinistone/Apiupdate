<?php
namespace App\Models;

class Principal extends User
{
    const ROLE = 'principal';
    const ROLE_ID = 5;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            return app()->make(static::class)->scopeRoleId($query, self::ROLE_ID);
        });

        static::observe(new PrincipalObserver);
    }
}
