<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\Facebook\FacebookConversionApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private readonly FacebookConversionApiService $facebook,
    ) {}

    /**
     * Tworzy rezerwację: bilet w statusie pending z blokadą miejsca na 15 minut oraz tokenem sesji płatności.
     */
    public function store(StoreBookingRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            /** @var Event $event */
            $event = Event::query()->lockForUpdate()->findOrFail($validated['event_id']);

            if ($event->takenSeatsCount() >= $event->total_seats) {
                return null;
            }

            $holdUntil = now()->addMinutes(15);
            $paymentToken = Str::random(48);

            $ticket = Ticket::query()->create([
                'event_id' => $event->id,
                'user_email' => $validated['email'],
                'status' => TicketStatus::Pending,
                'unique_code' => $this->generateUniqueCode(),
                'qr_code_path' => null,
                'payment_token' => $paymentToken,
                'hold_expires_at' => $holdUntil,
            ]);

            return [
                'ticket' => $ticket,
                'payment_token' => $paymentToken,
                'hold_expires_at' => $holdUntil,
            ];
        });

        if ($result === null) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Brak wolnych miejsc dla tego wydarzenia.',
                ], 422);
            }

            return redirect()->back()->withErrors(['booking' => 'Brak wolnych miejsc dla tego wydarzenia.'])->withInput();
        }

        /** @var Ticket $ticket */
        $ticket = $result['ticket'];
        $ticket->load('event');

        if ($this->facebook->isConfigured()) {
            $this->facebook->initiateCheckout(
                (float) $ticket->event->price,
                'PLN',
                $request->fullUrl(),
            );
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Rezerwacja utworzona. Dokończ płatność w ciągu 15 minut.',
                'ticket_id' => $ticket->id,
                'payment_token' => $result['payment_token'],
                'hold_expires_at' => $result['hold_expires_at']->toIso8601String(),
            ], 201);
        }

        return redirect()->back()->with('booking_success', 'Rezerwacja utworzona. Użyj tokena płatności, aby przejść do Przelewy24 (endpoint payment.init).')
            ->with('payment_token', $result['payment_token']);
    }

    private function generateUniqueCode(): string
    {
        do {
            $hash = Str::lower(Str::random(40));
        } while (Ticket::query()->where('unique_code', $hash)->exists());

        return $hash;
    }
}
