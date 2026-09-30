<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\ApiConfigResource;

class ListApiConfigs extends ListRecords
{
    protected static string $resource = ApiConfigResource::class;

    protected static ?string $title = 'API Data Pipeline Configs';

    protected ?string $subheading = 'Set up source endpoints, data mappings, and run dependency structures.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
