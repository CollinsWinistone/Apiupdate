<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomAutoTask extends Model
{
    protected $table = 'classroom_creation_tasks';

    protected $guarded = [];

    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
}
