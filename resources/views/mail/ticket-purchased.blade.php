<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: DejaVu Sans, sans-serif; line-height: 1.6; color: #1a1a1a;">
    <p>Cześć,</p>
    <p>Płatność za bilet na wydarzenie <strong>{{ $ticket->event->title }}</strong> została zaksięgowana.</p>
    <p>W załączeniu znajdziesz bilet PDF z kodem QR — pokaż go przy wejściu na koncert.</p>
    <p style="margin-top: 2rem; color: #666; font-size: 0.9rem;">Studio Nagrań Recpublica</p>
</body>
</html>
