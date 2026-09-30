<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Rimba\Sync\Http\UI\Admin\Resources\WorkforceSnapshots\WorkforceSnapshotResource;

class ListWorkforceSnapshots extends ListRecords
{
    protected static string $resource = WorkforceSnapshotResource::class;

    protected static ?string $title = 'Workforce Snapshots';

    protected ?string $subheading = 'Capture raw background point-in-time reference payloads and checksums.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
