<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;

class TicketBulkPdfGenerator
{
    public function __construct(
        private readonly TicketGenerator $ticketGenerator,
    ) {}

    /**
     * Łączy wiele biletów w jeden dokument PDF (osobna strona na bilet).
     */
    public function render(Collection $tickets): string
    {
        $chunks = [];
        foreach ($tickets as $i => $ticket) {
            if (! $ticket instanceof Ticket) {
                continue;
            }
            $body = $this->ticketGenerator->renderHtmlBody($ticket);
            if ($i > 0) {
                $chunks[] = '<div style="page-break-after: always;"></div>';
            }
            $chunks[] = $body;
        }

        $html = '<!DOCTYPE html><html lang="pl"><head><meta charset="utf-8"></head><body>'.implode('', $chunks).'</body></html>';

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'chroot' => storage_path('app'),
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
