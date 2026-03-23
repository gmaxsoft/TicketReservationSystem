<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketPurchasedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $pdfRelativePath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Twój bilet — Studio Nagrań Recpublica',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ticket-purchased',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->pdfRelativePath)
                ->as('bilet-recpublica.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
