<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Rimba\Sync\Http\UI\Admin\Resources\WorkforceChanges\WorkforceChangeResource;

class ListWorkforceChanges extends ListRecords
{
    protected static string $resource = WorkforceChangeResource::class;

    protected static ?string $title = 'Workforce Change Logs';

    protected ?string $subheading = 'Audit detected field modifications before and after application processing.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
