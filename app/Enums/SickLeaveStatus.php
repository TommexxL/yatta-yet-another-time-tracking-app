<?php

namespace App\Enums;

enum SickLeaveStatus: string
{
    case Reported = 'reported';
    case Approved = 'approved';
    case Denied = 'denied';
    case Incomplete = 'incomplete';
}
