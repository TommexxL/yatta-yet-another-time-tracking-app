<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'schedule_id',
    'weekday',
    'start_time',
    'end_time',
    'break_minutes',
])]
class ScheduleDay extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleDayFactory> */
    use HasFactory;

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'break_minutes' => 'integer',
        ];
    }
}
