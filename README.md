# System rezerwacji biletów (TRS)

Aplikacja webowa do sprzedaży biletów na wydarzenia muzyczne, z panelem administracyjnym (Filament), płatnościami Przelewy24, integracją z ogłoszeniami OLX oraz obsługą wejścia (QR / skaner).

## Stack technologiczny

| Warstwa | Technologie |
|--------|-------------|
| **Backend** | [Laravel](https://laravel.com) 13, PHP 8.3+ |
| **Panel admina** | [Filament](https://filamentphp.com) v5 (Livewire, Alpine.js, Tailwind CSS wg pakietu Filament) |
| **Baza danych** | MySQL (produkcja / rozwój); SQLite w pamięci w testach PHPUnit |
| **ORM / sesja / kolejka** | Eloquent; sesja i cache możliwe na sterowniku `database` (tabele z migracji Laravel) |
| **Płatności** | Przelewy24 (pakiet `mnastalski/przelewy24-php`, lokalna ścieżka `przelewy24/`) |
| **PDF i QR** | [Dompdf](https://github.com/dompdf/dompdf) 3.x, [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode) 5.x |
| **OLX** | Partner API (`api_olx/`, OAuth2 / bridge w `packages/olx-oauth-bridge/`) |
| **Marketing** | Facebook Conversion API (serwis w `app/Services/Facebook/`) |
| **Frontend** | Blade, szablony widoków; skaner QR: [html5-qrcode](https://github.com/mebjas/html5-qrcode) (CDN na stronie Scanner) |
| **Narzędzia dev** | PHPUnit 12, Laravel Pint, Collision, Faker |

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
   php artisan db:seed
   ```

   `db:seed` tworzy konto administratora opisane poniżej (logowanie do Filament).

6. Dowiązanie publicznego storage (plakaty, pliki publiczne):

   ```bash
   php artisan storage:link
   ```

7. (Opcjonalnie) Zamiast seedera możesz utworzyć konto ręcznie:

   ```bash
   php artisan filament:user
   ```

## Panel administracyjny (Filament)

- Domyślna ścieżka: **`/admin`**
- Strona główna **`/`** przekierowuje gościa do logowania panelu; zalogowany użytkownik trafia na pulpit.
- W panelu można m.in. zarządzać wydarzeniami, biletami, ogłoszeniami OLX oraz korzystać ze strony **Scanner** (odczyt kodów QR z kamery).

### Logowanie (konto z seedera)

Po wykonaniu `php artisan db:seed` możesz zalogować się tymi danymi:

| Pole | Wartość |
|------|---------|
| **E-mail** | `admin@trs.local` |
| **Hasło** | `TRS_k9Lm#2pQxW` |

**Bezpieczeństwo:** To konto jest przeznaczone do pracy lokalnej / deweloperskiej. **Przed wdrożeniem produkcyjnym zmień hasło** (np. w panelu lub przez `php artisan tinker`) albo usuń użytkownika i utwórz nowe konto. Nie używaj tych samych danych na publicznie dostępnym serwerze.

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
