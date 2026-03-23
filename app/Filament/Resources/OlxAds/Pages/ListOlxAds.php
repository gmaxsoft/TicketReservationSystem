<?php

namespace App\Filament\Resources\OlxAds\Pages;

use App\Filament\Resources\OlxAds\OlxAdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOlxAds extends ListRecords
{
    protected static string $resource = OlxAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
