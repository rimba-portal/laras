<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\Pages\ListWorkforceSnapshots;
use Rimba\Sync\Models\WorkforceSnapshot;
use UnitEnum;

class WorkforceSnapshotResource extends Resource
{
    protected static ?string $model = WorkforceSnapshot::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 41;

    protected static ?string $recordTitleAttribute = 'checksum';

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
            'index' => ListWorkforceSnapshots::route('/'),
            // 'create' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\Pages\CreateWorkforceSnapshot::route('/create'),
            // 'view' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\Pages\ViewWorkforceSnapshot::route('/{record}'),
            // 'edit' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\Pages\EditWorkforceSnapshot::route('/{record}/edit'),
            //
        ];
    }
}
