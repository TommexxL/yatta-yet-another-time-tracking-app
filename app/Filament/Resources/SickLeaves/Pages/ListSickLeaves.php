<?php

namespace App\Filament\Resources\SickLeaves\Pages;

use App\Filament\Resources\SickLeaves\SickLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSickLeaves extends ListRecords
{
    protected static string $resource = SickLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
