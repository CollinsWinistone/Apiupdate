<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $table = 'publishers';

    protected $primaryKey = 'pbl_id';

    public $timestamps = false;
}
