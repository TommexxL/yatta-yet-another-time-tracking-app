<?php

namespace App\Filament\Resources\SickLeaves\Pages;

use App\Filament\Resources\SickLeaves\SickLeaveResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSickLeave extends EditRecord
{
    protected static string $resource = SickLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
