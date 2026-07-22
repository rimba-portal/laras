<?php

declare(strict_types=1);

namespace Rimba\Sync\Http\UI\Admin\Resources\ApiData\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApiDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('api_config_id')
                    ->relationship('apiConfig', 'name')
                    ->required(),
                TextInput::make('fingerprint'),
                Textarea::make('payload')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('processed_at'),
                Textarea::make('error')
                    ->columnSpanFull(),
            ]);
    }
}
