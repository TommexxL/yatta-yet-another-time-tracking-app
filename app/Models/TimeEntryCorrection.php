<?php

namespace App\Models;

use App\Enums\TimeEntryCorrectionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'user_id',
    'time_entry_id',
    'date',
    'current_clock_in',
    'current_clock_out',
    'requested_clock_in',
    'requested_clock_out',
    'reason',
    'status',
    'reviewed_by',
    'reviewed_at',
    'manager_notes',
])]
class TimeEntryCorrection extends Model
{
    /** @use HasFactory<\Database\Factories\TimeEntryCorrectionFactory> */
    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reviewed_at' => 'datetime',
            'status' => TimeEntryCorrectionStatus::class,
        ];
    }
}
