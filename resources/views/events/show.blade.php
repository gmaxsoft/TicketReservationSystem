<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->title }}</title>
</head>
<body style="font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 0 1rem;">
    <h1>{{ $event->title }}</h1>
    @if($event->description)
        <p>{{ $event->description }}</p>
    @endif
    <p><strong>Data:</strong> {{ $event->event_date?->format('d.m.Y H:i') }}</p>
    <p><strong>Cena:</strong> {{ number_format((float) $event->price, 2, ',', ' ') }} PLN</p>
    <p><strong>Wolne miejsca:</strong> ok. {{ $availableSeats }}</p>

    @if ($errors->any())
        <div style="color: #b00020;">
            @foreach ($errors->all() as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    @if (session('booking_success'))
        <p style="color: #1b5e20;">{{ session('booking_success') }}</p>
        @if (session('payment_token'))
            <p><strong>Token płatności (dla API):</strong> {{ session('payment_token') }}</p>
        @endif
    @endif

    <h2>Rezerwacja</h2>
    <form method="post" action="{{ route('booking.store') }}" style="display: grid; gap: 0.75rem;">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event->id }}">
        <label>E-mail <input type="email" name="email" required style="width: 100%; padding: 0.5rem;"></label>
        <button type="submit">Zarezerwuj</button>
    </form>
</body>
</html>
