<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Widgets\EventTicketsSoldWidget;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            EventTicketsSoldWidget::make(['record' => $this->getRecord()]),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $header = $this->headerWidgets(Schema::make($this));
        $main = parent::content($schema);

        return $schema->components([
            ...$header->getComponents(),
            ...$main->getComponents(),
        ]);
    }
}
