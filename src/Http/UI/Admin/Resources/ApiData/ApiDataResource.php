<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiData;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages\CreateApiData;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages\EditApiData;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages\ListApiData;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\Schemas\ApiDataForm;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\Tables\ApiDataTable;
use Rimba\Sync\Models\ApiData;
use UnitEnum;

class ApiDataResource extends Resource
{
    protected static ?string $model = ApiData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Data Synchronization';

    protected static ?string $navigationLabel = 'Data';

    protected static ?int $navigationSort = 42;

    public static function form(Schema $schema): Schema
    {
        return ApiDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiDataTable::configure($table);
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
            'index' => ListApiData::route('/'),
            'create' => CreateApiData::route('/create'),
            'edit' => EditApiData::route('/{record}/edit'),
        ];
    }
}
