<?php

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiDatas;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApiDataResource extends Resource
{
    protected static ?string $model = \Rimba\Sync\Models\ApiData::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 39;

    protected static ?string $recordTitleAttribute = 'fingerprint';

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
            'index' => \Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\Pages\ListApiDatas::route('/'),
            // 'create' => \Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\Pages\CreateApiData::route('/create'),
            // 'view' => \Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\Pages\ViewApiData::route('/{record}'),
            // 'edit' => \Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\Pages\EditApiData::route('/{record}/edit'),
            //
        ];
    }
}
