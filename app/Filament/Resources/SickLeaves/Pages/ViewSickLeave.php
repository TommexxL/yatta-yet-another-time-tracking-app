<?php

namespace App\Filament\Resources\SickLeaves\Pages;

use App\Filament\Resources\SickLeaves\SickLeaveResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSickLeave extends ViewRecord
{
    protected static string $resource = SickLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
