<?php

namespace App\Http\Controllers\Payment;

use App\Enums\TicketStatus;
use App\Events\PaymentConfirmed;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Facebook\FacebookConversionApiService;
use App\Services\Przelewy24\Przelewy24Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Przelewy24\Constants\IpAddresses;
use Przelewy24\Exceptions\Przelewy24Exception;

class Przelewy24WebhookController extends Controller
{
    public function __invoke(
        Request $request,
        Przelewy24Factory $factory,
        FacebookConversionApiService $facebook,
    ): Response {
        if (config('przelewy24.verify_webhook_ip') && ! IpAddresses::isValid($request->ip())) {
            abort(403, 'Niedozwolony adres IP.');
        }

        $p24 = $factory->make();
        $payload = $request->all();

        try {
            $webhook = $p24->handleWebhook($payload);
        } catch (\Throwable $e) {
            return response('Nieprawidłowy payload', 400);
        }

        $sessionId = $webhook->sessionId();

        $ticket = Ticket::query()->where('payment_token', $sessionId)->first();
        if ($ticket === null) {
            return response('Nie znaleziono rezerwacji', 404);
        }

        if ($ticket->status === TicketStatus::Paid) {
            return response('OK', 200);
        }

        if ($ticket->status !== TicketStatus::Pending) {
            return response('Nieprawidłowy status biletu', 409);
        }

        $currency = $webhook->currency();

        $signValid = $webhook->isSignValid(
            $sessionId,
            $webhook->amount(),
            $webhook->originAmount(),
            $webhook->orderId(),
            $webhook->methodId(),
            $webhook->statement(),
            $currency,
        );

        if (! $signValid) {
            return response('Nieprawidłowy podpis', 400);
        }

        $ticket->load('event');
        $expectedAmount = (int) round((float) $ticket->event->price * 100);

        if ($webhook->amount() !== $expectedAmount) {
            return response('Niezgodna kwota', 400);
        }

        try {
            DB::transaction(function () use ($p24, $webhook, $ticket, $expectedAmount, $currency, $sessionId): void {
                $p24->transactions()->verify(
                    $sessionId,
                    $webhook->orderId(),
                    $expectedAmount,
                    $currency,
                );

                $ticket->status = TicketStatus::Paid;
                $ticket->payment_id = $webhook->orderId();
                $ticket->price_paid = $ticket->event->price;
                $ticket->hold_expires_at = null;
                $ticket->paid_at = now();
                $ticket->save();
            });
        } catch (Przelewy24Exception $e) {
            report($e);

            return response('Weryfikacja Przelewy24 nie powiodła się', 502);
        }

        $ticket->refresh();
        $ticket->loadMissing('event');

        PaymentConfirmed::dispatch($ticket);

        if ($facebook->isConfigured()) {
            $facebook->purchase(
                (float) $ticket->event->price,
                'PLN',
                $ticket->user_email,
                route('payment.return', ['token' => $ticket->payment_token]),
            );
        }

        return response('OK', 200);
    }
}
