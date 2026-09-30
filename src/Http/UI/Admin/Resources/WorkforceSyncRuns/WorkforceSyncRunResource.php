<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\Pages\ListWorkforceSyncRuns;
use Rimba\Sync\Models\WorkforceSyncRun;
use UnitEnum;

class WorkforceSyncRunResource extends Resource
{
    protected static ?string $model = WorkforceSyncRun::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 42;

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
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
            'index' => ListWorkforceSyncRuns::route('/'),
            // 'create' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\Pages\CreateWorkforceSyncRun::route('/create'),
            // 'view' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\Pages\ViewWorkforceSyncRun::route('/{record}'),
            // 'edit' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\Pages\EditWorkforceSyncRun::route('/{record}/edit'),
            //
        ];
    }
}
