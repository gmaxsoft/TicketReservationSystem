<?php

namespace App\Filament\Resources\Events\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventTicketsSoldWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    public ?Event $record = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        if (! $this->record instanceof Event) {
            return [
                Stat::make(__('Sprzedane bilety'), '0'),
            ];
        }

        return [
            Stat::make(__('Sprzedane bilety'), (string) $this->record->ticketsSoldCount()),
        ];
    }
}
