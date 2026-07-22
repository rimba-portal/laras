<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiData\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rimba\Sync\Http\UI\Admin\Resources\ApiData\ApiDataResource;

class CreateApiData extends CreateRecord
{
    protected static string $resource = ApiDataResource::class;
}
