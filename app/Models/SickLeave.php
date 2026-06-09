<?php

namespace App\Models;

use App\Enums\SickLeaveStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'user_id',
    'start_date',
    'expected_return_date',
    'end_date',
    'notes',
    'status',
])]
class SickLeave extends Model
{
    /** @use HasFactory<\Database\Factories\SickLeaveFactory> */
    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_return_date' => 'date',
            'end_date' => 'date',
            'status' => SickLeaveStatus::class,
        ];
    }
}
