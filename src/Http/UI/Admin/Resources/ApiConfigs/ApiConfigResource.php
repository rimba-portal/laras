<?php

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApiConfigResource extends Resource
{
    protected static ?string $model = \Rimba\Sync\Models\ApiConfig::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static ?int $navigationSort = 38;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema { return \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Schemas\ApiConfigForm::configure($schema); }

    public static function infolist(Schema $schema): Schema { return \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Schemas\ApiConfigInfolist::configure($schema); }

    public static function table(Table $table): Table { return \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Tables\ApiConfigsTable::configure($table); }

    public static function getRelations(): array 
    { 
        return [ 
            // 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages\ListApiConfigs::route('/'),
             'create' => \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages\CreateApiConfig::route('/create'),
             'view' => \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages\ViewApiConfig::route('/{record}'),
             'edit' => \Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages\EditApiConfig::route('/{record}/edit'),
            //
        ];
    }
}
