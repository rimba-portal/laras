<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Pages;

use Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\ApiConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApiConfig extends CreateRecord
{
    protected static string $resource = ApiConfigResource::class;
}
