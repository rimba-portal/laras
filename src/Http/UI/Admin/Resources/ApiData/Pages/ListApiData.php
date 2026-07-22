<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\ApiDataResource;

class ListApiData extends ListRecords
{
    protected static string $resource = ApiDataResource::class;

    protected static ?string $title = 'Data';

    protected ?string $subheading = 'Data Synchronization from external.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
