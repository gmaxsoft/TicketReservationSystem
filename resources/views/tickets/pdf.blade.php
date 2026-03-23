<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0d0d0d;
            margin: 0;
            padding: 0;
        }
        .wrap {
            border: 3px solid #c9a227;
            padding: 28px 32px;
            min-height: 680px;
            box-sizing: border-box;
            background: #faf8f3;
        }
        .brand {
            letter-spacing: 0.35em;
            font-size: 11px;
            text-transform: uppercase;
            color: #8a7a50;
            margin-bottom: 8px;
        }
        h1 {
            font-size: 26px;
            margin: 0 0 6px 0;
            font-weight: 700;
        }
        .subtitle {
            color: #555;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .row {
            width: 100%;
            margin-top: 18px;
        }
        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #888;
        }
        .value {
            font-size: 15px;
            margin-top: 4px;
        }
        .qr {
            text-align: center;
            margin-top: 28px;
        }
        .qr img {
            width: 220px;
            height: 220px;
        }
        .hash {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            color: #444;
            word-break: break-all;
            margin-top: 12px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e0d4;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">Studio Nagrań Recpublica</div>
        <h1>{{ $event->title }}</h1>
        <div class="subtitle">Bilet wstępu — jednorazowy</div>

        <table class="row" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td width="50%">
                    <div class="label">Data wydarzenia</div>
                    <div class="value">{{ $event->event_date?->format('d.m.Y H:i') }}</div>
                </td>
                <td width="50%">
                    <div class="label">E-mail</div>
                    <div class="value">{{ $ticket->user_email }}</div>
                </td>
            </tr>
        </table>

        <div class="row">
            <div class="label">Identyfikator biletu</div>
            <div class="value">#{{ $ticket->id }}</div>
        </div>

        <div class="qr">
            <img src="{{ $qrDataUri }}" alt="QR">
            <div class="hash">{{ $ticket->unique_code }}</div>
        </div>

        <div class="footer">
            Niniejszy dokument uprawnia do jednorazowego wejścia. Kod QR jest unikalny — nie udostępniaj go osobom trzecim.
        </div>
    </div>
</body>
</html>
