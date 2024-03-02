<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class StudentParent extends Model
{
    protected $table = 'student_parents';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $timestamps = true;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_usr_id', 'usr_id')->withoutGlobalScopes();
    }

    public function parents()
    {
        return $this->belongsTo(Parents::class, 'parent_usr_id', 'usr_id')->withoutGlobalScopes();
    }

    public function accept()
    {
        $student = $this->student()->first();
        $parent  = $this->parents()->first();

        if (!$parent->schools()->whereSchId($student->getSchoolId())->count()) {
            $parent->schools()->attach(
                $student->getSchoolId(), [
                    'group_id'  => Parents::ROLE_ID,
                    'active'    => 1
                ]
            );
        }

        $this->declined = 0;
        $this->accepted = 1;
        $this->save();
    }

    public function decline()
    {
        $this->declined = 1;
        $this->accepted = 0;
        $this->save();
    }

    public function isPending()
    {
        return (!$this->declined && !$this->accepted);
    }

    public function delete()
    {
        $parent = $this->parents()->first();
        if (!$parent->is_active) {
            $parent->forceDelete();
        }
        return parent::delete();
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder|static
     */
    public function scopeByParentId(Builder $query, $value)
    {
        return $query->where('parent_usr_id', $value);
    }

    public function getHash()
    {
        return Hash::make(
            $this->getKey()
            . $this->id
            . $this->student_usr_id
            . $this->parent_usr_id
            . $this->created_at
        );
    }
}
