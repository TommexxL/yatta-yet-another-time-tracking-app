<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class TimeEntryForm
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
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('clock_in'),
                TimePicker::make('clock_out'),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
