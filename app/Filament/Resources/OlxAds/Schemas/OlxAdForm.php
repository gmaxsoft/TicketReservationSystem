<?php

namespace App\Filament\Resources\OlxAds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OlxAdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->label('Wydarzenie')
                    ->required(),
                TextInput::make('olx_ad_id')
                    ->label('UUID OLX')
                    ->required(),
                TextInput::make('status')
                    ->label('Status lokalny')
                    ->required(),
                TextInput::make('olx_external_id')
                    ->label('OLX external ID'),
                TextInput::make('link')
                    ->label('Link do ogłoszenia')
                    ->url()
                    ->maxLength(2048),
                DateTimePicker::make('last_sync_at')
                    ->label('Ostatnia synchronizacja')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('advert_data_preview')
                    ->label('Dane z API (podgląd, tylko do odczytu)')
                    ->columnSpanFull()
                    ->rows(14)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
