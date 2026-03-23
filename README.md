# System rezerwacji biletów (TRS)

Aplikacja webowa do sprzedaży biletów na wydarzenia, z panelem administracyjnym (Filament), płatnościami Przelewy24, integracją z ogłoszeniami OLX oraz obsługą wejścia (QR / skaner).

## Wymagania

- PHP **8.3+**
- **Composer**
- **MySQL** (główne źródło danych)
- (opcjonalnie) Node.js — tylko jeśli budujesz assety frontowe

## Instalacja

1. Sklonuj repozytorium i przejdź do katalogu projektu.

2. Zainstaluj zależności:

   ```bash
   composer install
   ```

3. Skopiuj plik środowiskowy i wygeneruj klucz aplikacji:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. W pliku **`.env`** ustaw m.in.:

   - `APP_URL` — adres aplikacji (np. `http://ticketreservationsystem.test`)
   - `DB_CONNECTION=mysql` oraz parametry połączenia: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `CACHE_STORE` i `SESSION_DRIVER` — np. `database` (wymagane są tabele cache/sessions z domyślnych migracji Laravel)

5. Utwórz bazę danych MySQL (np. `utf8mb4_unicode_ci`), następnie:

   ```bash
   php artisan migrate
   ```

6. Dowiązanie publicznego storage (plakaty, pliki publiczne):

   ```bash
   php artisan storage:link
   ```

7. Konto administratora panelu:

   ```bash
   php artisan filament:user --email=admin@example.com --password=TwojeHaslo123 --name="Admin"
   ```

## Panel administracyjny (Filament)

- Domyślna ścieżka: **`/admin`**
- Strona główna **`/`** przekierowuje gościa do logowania panelu; zalogowany użytkownik trafia na pulpit.
- W panelu można m.in. zarządzać wydarzeniami, biletami, ogłoszeniami OLX oraz korzystać ze strony **Scanner** (odczyt kodów QR z kamery).

## Główne funkcje

| Obszar | Opis |
|--------|------|
| **Wydarzenia** | Tytuł, opis, data, liczba miejsc, cena, slug, aktywność, plakat |
| **Bilety** | Statusy: oczekujący, opłacony, anulowany, zrealizowany; kod biletu; powiązanie z płatnością |
| **Płatności** | Przelewy24 (inicjacja płatności, webhook potwierdzający transakcję) |
| **PDF / e-mail** | Generowanie biletu w PDF z kodem QR |
| **Check-in** | Ścieżka dla personelu (`/koncert`) — osobna autoryzacja; oznaczanie wejścia na podstawie kodu |
| **Scanner (admin)** | Strona w panelu z biblioteką html5-qrcode; endpoint do realizacji biletu |
| **OLX** | Integracja z Partner API (w katalogu `api_olx`); synchronizacja metadanych ogłoszeń |

## Konfiguracja dodatkowa

- **Przelewy24** — zmienne w `.env` zgodnie z `config/przelewy24.php` (sandbox/produkcja).
- **OLX** — pliki konfiguracji i tokeny zgodnie z `config/olx.php` oraz dokumentacją w `api_olx`.
- **Facebook CAPI** — opcjonalnie, konfiguracja w serwisie konwersji.

## Testy

```bash
php artisan test
```

Środowisko testowe (`phpunit.xml`) domyślnie używa bazy **SQLite w pamięci** — nie wymaga MySQL.

## Licencja

Projekt oparty na szablonie Laravel; szczegóły w pliku `LICENSE` (jeśli jest dołączony).
