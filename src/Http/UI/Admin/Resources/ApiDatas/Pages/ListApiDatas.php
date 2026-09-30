<?php

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApiDatas extends ListRecords
{
    protected static string $resource = \Rimba\Sync\Http\UI\Admin\Resources\ApiDatas\ApiDataResource::class;

    protected static ?string $title = 'API Payloads & Staging';

    protected ?string $subheading = 'Inspect raw payloads, validation hashes, and data process statuses.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
