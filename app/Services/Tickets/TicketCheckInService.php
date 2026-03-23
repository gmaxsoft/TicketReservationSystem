<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;

class TicketCheckInService
{
    /**
     * @return array{ok: bool, message: string, ticket: ?Ticket}
     */
    public function attempt(string $rawCode): array
    {
        $code = trim($rawCode);

        if ($code === '') {
            return ['ok' => false, 'message' => 'Podaj kod biletu.', 'ticket' => null];
        }

        $ticket = Ticket::query()
            ->where('unique_code', $code)
            ->with('event')
            ->first();

        if ($ticket === null) {
            return ['ok' => false, 'message' => 'Nie znaleziono biletu z tym kodem.', 'ticket' => null];
        }

        if ($ticket->status === TicketStatus::Used) {
            return [
                'ok' => false,
                'message' => 'Bilet został już wcześniej wykorzystany ('.$ticket->checked_in_at?->format('Y-m-d H:i:s').').',
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status !== TicketStatus::Paid) {
            return ['ok' => false, 'message' => 'Bilet nie jest opłacony.', 'ticket' => $ticket];
        }

        $ticket->update([
            'status' => TicketStatus::Used,
            'checked_in_at' => now(),
        ]);
        $ticket->refresh();

        return [
            'ok' => true,
            'message' => 'Check-in zapisany: '.$ticket->user_email.' — '.$ticket->event->title,
            'ticket' => $ticket,
        ];
    }
}
