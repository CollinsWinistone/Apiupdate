<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserRewardRate extends Model
{
    protected $table = 'user_rewards_rates';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $timestamps = false;

    const COURSE    = 'course';
    const BONUS     = 'bonus';
    const TEACHER   = 'teacher';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id', 'id');
    }
}
