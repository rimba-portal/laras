<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages;

use Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\ApiConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApiConfigs extends ListRecords
{
    protected static string $resource = ApiConfigResource::class;

    protected static ?string $title = 'Configurations';

    protected ?string $subheading = 'Configuration settings for data synchronization from external sources.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
