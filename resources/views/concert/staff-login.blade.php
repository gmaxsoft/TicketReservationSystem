<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Obsługa koncertu — logowanie</title>
</head>
<body style="font-family: system-ui, sans-serif; max-width: 420px; margin: 3rem auto; padding: 0 1rem;">
    <h1>Obsługa koncertu</h1>
    <p>Wpisz hasło, aby przejść do check-inu.</p>

    @if ($errors->any())
        <p style="color: #b00020;">{{ $errors->first('password') }}</p>
    @endif

    <form method="post" action="{{ url('/koncert/logowanie') }}" style="display: grid; gap: 0.75rem;">
        @csrf
        <label>Hasło <input type="password" name="password" required autocomplete="current-password" style="width: 100%; padding: 0.5rem;"></label>
        <button type="submit">Wejdź</button>
    </form>
</body>
</html>
