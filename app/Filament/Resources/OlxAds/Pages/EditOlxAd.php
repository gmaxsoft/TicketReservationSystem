<?php

namespace App\Filament\Resources\OlxAds\Pages;

use App\Filament\Resources\OlxAds\OlxAdResource;
use App\Models\OlxAd;
use App\Services\Olx\OlxService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOlxAd extends EditRecord
{
    protected static string $resource = OlxAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncOlx')
                ->label('Sync with OLX')
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    /** @var OlxAd $record */
                    $record = $this->getRecord();
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
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['advert_data_preview'] = isset($data['advert_data']) && is_array($data['advert_data'])
            ? json_encode($data['advert_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : '';

        return $data;
    }
}
