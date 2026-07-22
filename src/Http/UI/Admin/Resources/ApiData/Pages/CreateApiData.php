<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages;

use Rimba\Sync\Http\UI\Admin\Resources\ApiData\ApiDataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApiData extends CreateRecord
{
    protected static string $resource = ApiDataResource::class;
}
