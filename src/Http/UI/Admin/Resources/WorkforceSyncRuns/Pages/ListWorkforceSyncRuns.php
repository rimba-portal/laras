<?php

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkforceSyncRuns extends ListRecords
{
    protected static string $resource = \Rimba\Sync\Http\UI\Admin\Resources\WorkforceSyncRuns\WorkforceSyncRunResource::class;

    protected static ?string $title = 'Workforce Synchronization Runs';

    protected ?string $subheading = 'Monitor high-level metrics showing synchronization metrics and errors.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
