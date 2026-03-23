<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_creates_pending_ticket_with_payment_token_and_hold(): void
    {
        $event = Event::query()->create([
            'title' => 'Koncert',
            'description' => null,
            'event_date' => now()->addWeek(),
            'total_seats' => 3,
            'price' => 99.50,
        ]);

        $response = $this->postJson(route('booking.store'), [
            'event_id' => $event->id,
            'email' => 'guest@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Rezerwacja utworzona. Dokończ płatność w ciągu 15 minut.')
            ->assertJsonStructure(['ticket_id', 'payment_token', 'hold_expires_at']);

        $token = $response->json('payment_token');
        $this->assertIsString($token);
        $this->assertSame(48, strlen($token));

        $ticket = Ticket::query()->findOrFail($response->json('ticket_id'));
        $this->assertSame('guest@example.com', $ticket->user_email);
        $this->assertSame($token, $ticket->payment_token);
        $this->assertNotNull($ticket->hold_expires_at);
        $this->assertTrue($ticket->hold_expires_at->isFuture());
        $this->assertTrue($ticket->hold_expires_at->lessThanOrEqualTo(now()->addMinutes(15)));
    }

    public function test_returns_422_when_no_seats_left(): void
    {
        $event = Event::query()->create([
            'title' => 'Pełny',
            'description' => null,
            'event_date' => now()->addWeek(),
            'total_seats' => 1,
            'price' => 10,
        ]);

        Ticket::query()->create([
            'event_id' => $event->id,
            'user_email' => 'a@a.pl',
            'status' => 'paid',
            'unique_code' => 'hash-one-seat-taken-xxxxxxxxxx',
            'payment_token' => null,
            'hold_expires_at' => null,
        ]);

        $response = $this->postJson(route('booking.store'), [
            'event_id' => $event->id,
            'email' => 'b@b.pl',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Brak wolnych miejsc dla tego wydarzenia.');
    }
}
