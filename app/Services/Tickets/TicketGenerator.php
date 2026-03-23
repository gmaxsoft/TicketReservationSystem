<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class TicketGenerator
{
    /**
     * Generuje PDF biletu i zapisuje go na dysku lokalnym. Zwraca ścieżkę względną (storage/app).
     */
    public function renderHtmlBody(Ticket $ticket): string
    {
        $ticket->loadMissing('event');

        $qrPngBinary = $this->qrPngBinary($ticket->unique_code);
        $qrDataUri = 'data:image/png;base64,'.base64_encode($qrPngBinary);

        return view('tickets.pdf', [
            'ticket' => $ticket,
            'event' => $ticket->event,
            'qrDataUri' => $qrDataUri,
        ])->render();
    }

    public function generate(Ticket $ticket): string
    {
        $html = $this->renderHtmlBody($ticket);

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'chroot' => storage_path('app'),
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $relativePath = 'tickets/ticket-'.$ticket->id.'.pdf';
        Storage::disk('local')->put($relativePath, $dompdf->output());

        return $relativePath;
    }

    private function qrPngBinary(string $payload): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($payload);
    }
}
