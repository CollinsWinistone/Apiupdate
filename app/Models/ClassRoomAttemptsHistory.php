<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoomAttemptsHistory extends Model
{
    protected $table = 'attempts_history';

    protected $casts = [
        'data' => 'array'
    ];

    public $timestamps = true;

    public static function attemptToArray(ClassRoomAttemptsCurrent $studentAttempt)
    {
        return [
            $studentAttempt->att_date,
            $studentAttempt->attempt_no,
            $studentAttempt->lesson_id,
            $studentAttempt->lesson_inst_id,
            $studentAttempt->unit_id,
            $studentAttempt->scored_points,
            $studentAttempt->lesson_points,
            $studentAttempt->pass_weight,
            $studentAttempt->pass,
            json_decode($studentAttempt->metadata, true),
            $studentAttempt->attempt_duration
        ];
    }

    public function getDataAttribute()
    {
        $data = $this->data;
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }

        $result = [];

        foreach ($data as $item) {
            $result[] = [
                'att_date'          => $item[0],
                'attempt_no'        => $item[1],
                'lesson_id'         => $item[2],
                'lesson_inst_id'    => $item[3],
                'unit_id'           => $item[4],
                'scored_points'     => $item[5],
                'lesson_points'     => $item[6],
                'pass_weight'       => $item[7],
                'pass'              => $item[8],
                'metadata'          => $item[9],
                'attempt_duration'  => $item[10]
            ];
        }
        return $result;
    }
}
