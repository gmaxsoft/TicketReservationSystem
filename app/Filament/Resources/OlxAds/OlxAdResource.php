<?php

namespace App\Filament\Resources\OlxAds;

use App\Filament\Resources\OlxAds\Pages\CreateOlxAd;
use App\Filament\Resources\OlxAds\Pages\EditOlxAd;
use App\Filament\Resources\OlxAds\Pages\ListOlxAds;
use App\Filament\Resources\OlxAds\Schemas\OlxAdForm;
use App\Filament\Resources\OlxAds\Tables\OlxAdsTable;
use App\Models\OlxAd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OlxAdResource extends Resource
{
    protected static ?string $model = OlxAd::class;

    protected static ?string $navigationLabel = 'Ogłoszenia OLX';

    protected static ?string $modelLabel = 'ogłoszenie OLX';

    protected static ?string $pluralModelLabel = 'Ogłoszenia OLX';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OlxAdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OlxAdsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOlxAds::route('/'),
            'create' => CreateOlxAd::route('/create'),
            'edit' => EditOlxAd::route('/{record}/edit'),
        ];
    }
}
