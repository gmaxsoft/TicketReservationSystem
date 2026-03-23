<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tytuł')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->maxLength(255)
                    ->nullable()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Opis')
                    ->columnSpanFull(),
                DateTimePicker::make('event_date')
                    ->label('Data wydarzenia')
                    ->required(),
                TextInput::make('total_seats')
                    ->label('Liczba miejsc')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->label('Cena')
                    ->required()
                    ->numeric()
                    ->prefix('PLN'),
                Toggle::make('is_active')
                    ->label('Aktywne (widoczne w sprzedaży)')
                    ->default(true),
                FileUpload::make('poster_path')
                    ->label('Plakat koncertu')
                    ->image()
                    ->disk('public')
                    ->directory('posters')
                    ->imageEditor()
                    ->columnSpanFull(),
            ]);
    }
}
