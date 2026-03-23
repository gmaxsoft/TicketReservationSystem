<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketStatus;
use App\Services\Tickets\TicketBulkPdfGenerator;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Wydarzenie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $status = $state instanceof TicketStatus
                            ? $state
                            : TicketStatus::from((string) $state);

                        return match ($status) {
                            TicketStatus::Paid => 'Opłacony',
                            TicketStatus::Pending => 'Oczekuje',
                            TicketStatus::Cancelled => 'Anulowany',
                            TicketStatus::Used => 'Zrealizowany',
                        };
                    })
                    ->color(function ($state): string {
                        $status = $state instanceof TicketStatus
                            ? $state
                            : TicketStatus::from((string) $state);

                        return match ($status) {
                            TicketStatus::Paid => 'success',
                            TicketStatus::Pending => 'warning',
                            TicketStatus::Cancelled => 'gray',
                            TicketStatus::Used => 'info',
                        };
                    })
                    ->sortable(),
                TextColumn::make('unique_code')
                    ->label('Kod biletu')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                TextColumn::make('paid_at')
                    ->label('Data zakupu')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Wydarzenie')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(TicketStatus::class),
            ])
            ->defaultSort('paid_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadPdf')
                        ->label('Pobierz PDF (zbiorczo)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            $binary = app(TicketBulkPdfGenerator::class)->render($records);

                            return response()->streamDownload(
                                static fn () => print ($binary),
                                'bilety-'.now()->format('Y-m-d-His').'.pdf',
                                ['Content-Type' => 'application/pdf'],
                            );
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
