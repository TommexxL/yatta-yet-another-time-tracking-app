<?php

namespace App\Filament\Resources\SickLeaves\Pages;

use App\Filament\Resources\SickLeaves\SickLeaveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSickLeave extends CreateRecord
{
    protected static string $resource = SickLeaveResource::class;
}
