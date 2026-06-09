<?php

namespace App\Enums;

enum TimeEntryStatus: string
{
    case Open = 'open';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Corrected = 'corrected';
}
