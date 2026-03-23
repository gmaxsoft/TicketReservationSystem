<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\TicketPurchasedMail;
use App\Services\Tickets\TicketGenerator;
use Illuminate\Support\Facades\Mail;

class GenerateTicketPdfAndEmail
{
    public function __construct(
        private readonly TicketGenerator $ticketGenerator,
    ) {}

    public function handle(PaymentConfirmed $event): void
    {
        $ticket = $event->ticket->fresh(['event']);
        if ($ticket === null) {
            return;
        }

        $relativePath = $this->ticketGenerator->generate($ticket);
        $ticket->update(['qr_code_path' => $relativePath]);

        Mail::to($ticket->user_email)->send(new TicketPurchasedMail($ticket, $relativePath));
    }
}
