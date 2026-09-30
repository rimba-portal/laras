<?php

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkforceChangeResource extends Resource
{
    protected static ?string $model = \Rimba\Sync\Models\WorkforceChange::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'field';

    public static function form(Schema $schema): Schema { return $schema->components([]); }

    public static function infolist(Schema $schema): Schema { return $schema->components([]); }

    public static function table(Table $table): Table { return $table->columns([]); }

    public static function getRelations(): array 
    { 
        return [ 
            // 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\Pages\ListWorkforceChanges::route('/'),
            // 'create' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\Pages\CreateWorkforceChange::route('/create'),
            // 'view' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\Pages\ViewWorkforceChange::route('/{record}'),
            // 'edit' => \Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\Pages\EditWorkforceChange::route('/{record}/edit'),
            //
        ];
    }
}
