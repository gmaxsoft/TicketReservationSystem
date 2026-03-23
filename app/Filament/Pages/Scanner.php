<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Scanner extends Page
{
    protected string $view = 'filament.pages.scanner';

    protected static ?string $title = 'Scanner';

    protected static ?string $navigationLabel = 'Scanner';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?int $navigationSort = 100;
}
