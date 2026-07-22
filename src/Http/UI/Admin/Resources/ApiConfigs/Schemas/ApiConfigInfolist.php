<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiConfigs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ApiConfigInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('source_type'),
                TextEntry::make('source_config')
                    ->columnSpanFull(),
                TextEntry::make('data_path')
                    ->placeholder('-'),
                TextEntry::make('depends_on')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('mapping')
                    ->columnSpanFull(),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
