<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('action')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('auditable_type'),
                TextInput::make('auditable_id')
                    ->numeric(),
                TextInput::make('metadata'),
            ]);
    }
}
