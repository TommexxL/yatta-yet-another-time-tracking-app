<?php

namespace App\Filament\Resources\SickLeaves\Schemas;

use App\Enums\SickLeaveStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SickLeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('expected_return_date'),
                DatePicker::make('end_date'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Select::make('status')
                    ->required()
                    ->options(SickLeaveStatus::class),
            ]);
    }
}
