<?php

namespace App\Enums;

enum TimeEntryCorrectionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
}
