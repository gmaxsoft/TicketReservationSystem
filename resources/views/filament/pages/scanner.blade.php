<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Skieruj aparat na kod QR z biletu. Po poprawnym odczycie bilet zostanie oznaczony jako zrealizowany (wejście / check-in).
        </p>
        <div
            wire:ignore
            id="scanner-reader"
            class="mx-auto min-h-[280px] w-full max-w-lg overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800"
        ></div>
        <p id="scanner-last-message" class="text-center text-sm text-gray-600 dark:text-gray-400" role="status"></p>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('scanner-reader');
            const statusEl = document.getElementById('scanner-last-message');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = @json(route('admin.scanner.fulfill'));
            let lastScanAt = 0;
            let busy = false;

            if (!el || !csrf) {
                return;
            }

            const html5 = new Html5Qrcode('scanner-reader');

            const onScan = async (decodedText) => {
                const now = Date.now();
                if (busy || now - lastScanAt < 2000) {
                    return;
                }
                lastScanAt = now;
                busy = true;
                statusEl.textContent = 'Przetwarzanie…';

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ code: decodedText }),
                    });
                    const data = await res.json().catch(() => ({}));
                    statusEl.textContent = data.message ?? (res.ok ? 'Zapisano.' : 'Błąd.');
                } catch (e) {
                    statusEl.textContent = 'Błąd połączenia z serwerem.';
                } finally {
                    busy = false;
                }
            };

            Html5Qrcode.getCameras()
                .then((cameras) => {
                    if (!cameras || cameras.length === 0) {
                        statusEl.textContent = 'Brak dostępnej kamery.';
                        return;
                    }
                    const cameraId = cameras[0].id;
                    return html5.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: { width: 260, height: 260 },
                        },
                        onScan,
                        () => {},
                    );
                })
                .catch(() => {
                    statusEl.textContent = 'Nie udało się uruchomić kamery (uprawnienia / HTTPS).';
                });
        });
    </script>
</x-filament-panels::page>
