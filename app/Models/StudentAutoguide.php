<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAutoguide extends Model
{
    protected $table = 'student_autoguide';

    protected $primaryKey = 'user_id';

    protected $guarded = [];

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'usr_id')->withoutGlobalScopes();
    }
}
