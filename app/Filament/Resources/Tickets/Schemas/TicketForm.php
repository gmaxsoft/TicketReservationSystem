<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Enums\TicketStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->label('Wydarzenie')
                    ->required(),
                TextInput::make('user_email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(TicketStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('unique_code')
                    ->label('Unikalny kod')
                    ->required(),
                TextInput::make('price_paid')
                    ->label('Cena zapłacona')
                    ->numeric()
                    ->prefix('PLN'),
                DateTimePicker::make('paid_at')
                    ->label('Data zakupu')
                    ->seconds(false),
                TextInput::make('qr_code_path')
                    ->label('Ścieżka PDF/QR'),
                DateTimePicker::make('hold_expires_at')
                    ->label('Rezerwacja wygasa'),
                TextInput::make('payment_id')
                    ->label('ID płatności (np. Przelewy24)'),
                DateTimePicker::make('checked_in_at')
                    ->label('Check-in'),
            ]);
    }
}
