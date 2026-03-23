<?php

namespace App\Filament\Resources\OlxAds\Tables;

use App\Models\OlxAd;
use App\Services\Olx\OlxService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OlxAdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Wydarzenie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('olx_ad_id')
                    ->label('UUID OLX')
                    ->searchable(),
                TextColumn::make('olx_external_id')
                    ->label('OLX external ID')
                    ->searchable(),
                TextColumn::make('link')
                    ->label('Link')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status lokalny')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_sync_at')
                    ->label('Ostatnia synchronizacja')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('api_sync_status')
                    ->label('Status API')
                    ->getStateUsing(fn (OlxAd $record): string => (string) data_get($record->advert_data, 'status.code', '—'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('syncOlx')
                    ->label('Sync with OLX')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (OlxAd $record): void {
                        try {
                            app(OlxService::class)->syncListingMetadata($record);
                            $record->refresh();
                            Notification::make()
                                ->title('Zsynchronizowano z OLX')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()
                                ->title('Błąd synchronizacji')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
