<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRewardTransaction extends Model
{
    protected $table = 'user_rewards_transactions';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'usr_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'usr_id');
    }

    public function storeTransaction()
    {
        return $this->belongsTo(Transaction::class, 'store_transaction_id', 'id');
    }
}
