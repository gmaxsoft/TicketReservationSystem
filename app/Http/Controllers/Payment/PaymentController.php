<?php

namespace App\Http\Controllers\Payment;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Przelewy24\Przelewy24Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Przelewy24\Enums\Encoding;
use Przelewy24\Enums\Language;
use Przelewy24\Exceptions\Przelewy24Exception;

class PaymentController extends Controller
{
    public function __construct(
        private readonly Przelewy24Factory $przelewy24Factory,
    ) {}

    /**
     * Rejestruje transakcję w Przelewy24 i zwraca URL do bramki płatności.
     */
    public function init(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_token' => ['required', 'string', 'size:48'],
        ]);

        $ticket = Ticket::query()
            ->where('payment_token', $data['payment_token'])
            ->where('status', TicketStatus::Pending)
            ->firstOrFail();

        if ($ticket->hold_expires_at !== null && $ticket->hold_expires_at->isPast()) {
            return response()->json([
                'message' => 'Sesja rezerwacji wygasła. Utwórz nową rezerwację.',
            ], 422);
        }

        $ticket->loadMissing('event');

        $amount = (int) round((float) $ticket->event->price * 100);

        $p24 = $this->przelewy24Factory->make();

        try {
            $registered = $p24->transactions()->register(
                sessionId: $ticket->payment_token,
                amount: $amount,
                description: 'Bilet: '.$ticket->event->title,
                email: $ticket->user_email,
                urlReturn: route('payment.return', ['token' => $ticket->payment_token]),
                encoding: Encoding::UTF_8,
                language: Language::POLISH,
                urlStatus: route('webhooks.przelewy24'),
                timeLimit: 15,
            );
        } catch (Przelewy24Exception $e) {
            report($e);

            return response()->json([
                'message' => 'Nie udało się uruchomić płatności. Spróbuj ponownie później.',
            ], 502);
        }

        return response()->json([
            'gateway_url' => $registered->gatewayUrl(),
        ]);
    }
}
