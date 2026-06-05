<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TimeEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.name')
                    ->label('Company'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('clock_in')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('clock_out')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
