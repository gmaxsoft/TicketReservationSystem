<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in — Recpublica</title>
</head>
<body style="font-family: system-ui, sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem;">
    <h1>Check-in na koncert</h1>
    <p>Zeskanuj kod QR z biletu kamerą lub wpisz kod ręcznie (pole <code>unique_code</code>).</p>

    <form method="post" action="{{ route('concert.check-in') }}" id="checkin-form" style="display: grid; gap: 1rem;">
        @csrf
        <div>
            <video id="video" playsinline style="width: 100%; max-height: 280px; background: #111; border-radius: 8px;"></video>
            <canvas id="canvas" hidden></canvas>
            <p><button type="button" id="start-camera" style="padding: 0.5rem 1rem;">Uruchom kamerę</button>
            <button type="button" id="stop-camera" style="padding: 0.5rem 1rem;">Zatrzymaj</button></p>
        </div>
        <label>Kod biletu
            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                   style="width: 100%; padding: 0.5rem; font-family: monospace;" autocomplete="off">
        </label>
        <button type="submit" style="padding: 0.6rem 1rem;">Potwierdź wejście</button>
    </form>

    @if ($errors->any())
        <div style="color: #b00020; margin-top: 1rem;">
            {{ $errors->first('code') }}
        </div>
    @endif

    @if (session('status'))
        <p style="color: #1b5e20; margin-top: 1rem;">{{ session('status') }}</p>
    @endif

    <p style="margin-top: 2rem;"><a href="{{ route('concert.staff.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Wyloguj</a></p>
    <form id="logout-form" action="{{ route('concert.staff.logout') }}" method="post" style="display:none;">
        @csrf
    </form>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const codeInput = document.getElementById('code');
        let stream = null;
        let raf = null;

        document.getElementById('start-camera').addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                await video.play();
                const tick = () => {
                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const result = jsQR(imageData.data, imageData.width, imageData.height);
                        if (result && result.data) {
                            codeInput.value = result.data.trim();
                            cancelAnimationFrame(raf);
                            return;
                        }
                    }
                    raf = requestAnimationFrame(tick);
                };
                raf = requestAnimationFrame(tick);
            } catch (e) {
                alert('Nie udało się uruchomić kamery: ' + e.message);
            }
        });

        document.getElementById('stop-camera').addEventListener('click', () => {
            if (raf) cancelAnimationFrame(raf);
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            video.srcObject = null;
        });
    </script>
</body>
</html>
