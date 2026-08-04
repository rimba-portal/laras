<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\ApiConfigResource;

class CreateApiConfig extends CreateRecord
{
    protected static string $resource = ApiConfigResource::class;

    // Custom

}
