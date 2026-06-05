<?php

namespace App\Filament\Resources\SickLeaves;

use App\Filament\Resources\SickLeaves\Pages\CreateSickLeave;
use App\Filament\Resources\SickLeaves\Pages\EditSickLeave;
use App\Filament\Resources\SickLeaves\Pages\ListSickLeaves;
use App\Filament\Resources\SickLeaves\Pages\ViewSickLeave;
use App\Filament\Resources\SickLeaves\Schemas\SickLeaveForm;
use App\Filament\Resources\SickLeaves\Schemas\SickLeaveInfolist;
use App\Filament\Resources\SickLeaves\Tables\SickLeavesTable;
use App\Models\SickLeave;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SickLeaveResource extends Resource
{
    protected static ?string $model = SickLeave::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'start_date';

    public static function form(Schema $schema): Schema
    {
        return SickLeaveForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SickLeaveInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SickLeavesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSickLeaves::route('/'),
            'create' => CreateSickLeave::route('/create'),
            'view' => ViewSickLeave::route('/{record}'),
            'edit' => EditSickLeave::route('/{record}/edit'),
        ];
    }
}
