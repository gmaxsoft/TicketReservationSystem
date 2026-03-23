<?php

namespace App\Services\Olx;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\OlxAd;
use Illuminate\Support\Facades\Log;
use OAuth2\Configuration;
use OAuth2\Exception\StorageException;
use OAuth2\OAuth2;
use OAuth2\TokenManager\FileTokenStorageService;
use OAuth2\TokenManager\TokenManager;
use ServiceAdvert\Api\AdvertAPI;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Model\Status\Code;
use ServiceAdvert\Model\Status\Status as AdvertStatus;
use ServiceAdvert\Service\AdvertService;

class OlxService
{
    private ?Configuration $configuration = null;

    private ?TokenManager $tokenManager = null;

    private ?AdvertAPI $advertApi = null;

    private ?AdvertService $advertService = null;

    private ?OlxPartnerHttpClient $httpClient = null;

    public function __construct(
        private readonly EloquentAdvertRepository $repository,
    ) {}

    /**
     * Tworzy rekord lokalny i publikuje ogłoszenie w Partner API na podstawie {@see Event}.
     */
    public function publishConcertListing(Event $event): OlxAd
    {
        $this->assertConfigReadable();
        $externalId = $this->makeExternalId($event);
        $payload = $this->buildOfferPayload($event, $externalId);

        $advert = new Advert([
            Advert::EXTERNAL_ID_FIELD_NAME => $externalId,
            Advert::JSON_PAYLOAD_FIELD_NAME => $payload,
            Advert::STATUS_FIELD_NAME => [
                AdvertStatus::CODE_FIELD_NAME => Code::NOT_INIT,
            ],
        ]);

        $this->repository->create($advert);

        $this->runAdvertServiceSilently(function (AdvertService $service) use ($advert): void {
            $service->publish($advert);
        });

        return OlxAd::query()->where('olx_external_id', $externalId)->firstOrFail();
    }

    /**
     * Aktualizuje treść ogłoszenia (m.in. liczba wolnych miejsc) przez PUT w Partner API.
     */
    public function updateListingAvailableSeats(Event $event): void
    {
        $this->assertConfigReadable();

        $olxAd = OlxAd::query()->where('event_id', $event->id)->firstOrFail();
        if ($olxAd->olx_external_id === null) {
            throw new \RuntimeException('Brak powiązania olx_external_id dla ogłoszenia OLX.');
        }

        $advert = $this->repository->read($olxAd->olx_external_id);
        if ($advert->getUuid() === '') {
            throw new \RuntimeException('Ogłoszenie nie ma jeszcze UUID z Partner API — opublikuj je najpierw.');
        }

        $payload = $advert->getJsonPayload();
        $payload['description'] = $this->buildDescription($event);
        if (isset($payload['price']) && is_array($payload['price'])) {
            $payload['price']['value'] = (float) $event->price;
        }
        $advert->setJsonPayload($payload);

        $this->runAdvertServiceSilently(function (AdvertService $service) use ($advert): void {
            $service->update($advert);
        });
    }

    /**
     * Pobiera wiadomości z endpointu Partner API (ścieżka w konfiguracji).
     * W wielu wdrożeniach OLX dostarcza czat przez webhook — wtedy ustaw OLX_CHAT_MESSAGES_PATH lub obsłuż webhook osobno.
     *
     * @return array<string, mixed>
     */
    public function fetchMessages(?OlxAd $olxAd = null): array
    {
        $path = config('olx.chat_messages_path');
        if ($path === null || $path === '') {
            Log::warning('OLX: OLX_CHAT_MESSAGES_PATH jest puste — brak żądania HTTP do pobrania wiadomości.');

            return [];
        }

        $this->assertConfigReadable();

        if (str_contains($path, '%s')) {
            $uuid = $olxAd?->olx_ad_id;
            if ($uuid === null || $uuid === '') {
                throw new \InvalidArgumentException('Podaj OlxAd z ustawionym olx_ad_id (UUID z API) albo ścieżkę bez %s.');
            }
            $path = sprintf($path, $uuid);
        }

        return $this->partnerHttpClient()->get($path);
    }

    /**
     * Synchronizuje metadane ogłoszenia z API (status, last_error itd.).
     */
    public function syncListingMetadata(OlxAd $olxAd): void
    {
        if ($olxAd->olx_external_id === null) {
            throw new \RuntimeException('Brak olx_external_id.');
        }

        $this->assertConfigReadable();
        $advert = $this->repository->read($olxAd->olx_external_id);

        $this->runAdvertServiceSilently(function (AdvertService $service) use ($advert): void {
            $service->syncMetadata($advert);
        });

        $olxAd->refresh();
        $olxAd->forceFill(['last_sync_at' => now()])->save();
    }

    public function availableSeatCount(Event $event): int
    {
        $reserved = $event->tickets()
            ->where('status', '!=', TicketStatus::Cancelled)
            ->count();

        return max(0, $event->total_seats - $reserved);
    }

    private function makeExternalId(Event $event): string
    {
        return 'evt-'.$event->id.'-'.uniqid();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOfferPayload(Event $event, string $externalId): array
    {
        $path = config('olx.payload_template_path');
        if (! is_readable($path)) {
            throw new \RuntimeException('Nie znaleziono szablonu payloadu OLX: '.$path);
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Nie można odczytać szablonu payloadu OLX: '.$path);
        }

        $json = $this->applyPayloadPlaceholders($raw, $event);
        /** @var array<string, mixed> $payload */
        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $payload['title'] = $event->title;
        $payload['description'] = $this->buildDescription($event);
        $payload['category_urn'] = config('olx.category_urn');
        $payload['site_urn'] = config('olx.site_urn');

        if (isset($payload['price']) && is_array($payload['price'])) {
            $payload['price']['value'] = (float) $event->price;
            $payload['price']['currency'] = config('olx.currency');
        }

        $payload[Advert::CUSTOM_FIELDS_NAME] = [
            Advert::ID_FIELD_NAME => $externalId,
        ];

        return $payload;
    }

    private function buildDescription(Event $event): string
    {
        $base = $event->description ?? '';
        $available = $this->availableSeatCount($event);
        $when = $event->event_date?->format('Y-m-d H:i') ?? '';

        $suffix = "\n\n—\nWolne miejsca: {$available} / {$event->total_seats}.\nData wydarzenia: {$when}.";

        return trim($base.$suffix);
    }

    private function applyPayloadPlaceholders(string $template, Event $event): string
    {
        $replacements = [
            '{{title}}' => $event->title,
            '{{description}}' => $event->description ?? '',
            '{{price_value}}' => (string) (float) $event->price,
            '{{available_seats}}' => (string) $this->availableSeatCount($event),
            '{{event_date}}' => $event->event_date?->format('Y-m-d H:i') ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function assertConfigReadable(): void
    {
        $path = config('olx.config_path');
        if (! is_readable($path)) {
            throw new \RuntimeException(
                'Brak pliku konfiguracji OLX. Skopiuj api_olx/config.json.example do storage/app/olx/config.json (lub ustaw OLX_CONFIG_PATH).'
            );
        }
    }

    private function configuration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = Configuration::fromFile(config('olx.config_path'));
        }

        return $this->configuration;
    }

    private function tokenManager(): TokenManager
    {
        if ($this->tokenManager !== null) {
            return $this->tokenManager;
        }

        $oauth = new OAuth2($this->configuration());
        $storage = new FileTokenStorageService(config('olx.token_path'));

        try {
            $this->tokenManager = new TokenManager($oauth, $storage);
        } catch (StorageException $e) {
            $code = $this->configuration()->getCode();
            if ($code !== null && $code !== '') {
                $this->tokenManager = new TokenManager($oauth, $storage, $code);
            } else {
                throw new \RuntimeException(
                    'Brak zapisanego tokenu OAuth OLX. Uruchom autoryzację (pole code w config.json) lub skopiuj plik tokenu do: '.config('olx.token_path'),
                    0,
                    $e
                );
            }
        }

        return $this->tokenManager;
    }

    private function advertApi(): AdvertAPI
    {
        if ($this->advertApi === null) {
            $this->advertApi = new AdvertAPI($this->tokenManager(), $this->configuration());
        }

        return $this->advertApi;
    }

    private function advertService(): AdvertService
    {
        if ($this->advertService === null) {
            $this->advertService = new AdvertService($this->repository, $this->advertApi());
        }

        return $this->advertService;
    }

    private function partnerHttpClient(): OlxPartnerHttpClient
    {
        if ($this->httpClient === null) {
            $this->httpClient = new OlxPartnerHttpClient($this->tokenManager(), $this->configuration());
        }

        return $this->httpClient;
    }

    /**
     * @param  callable(AdvertService): void  $callback
     */
    private function runAdvertServiceSilently(callable $callback): void
    {
        ob_start();
        try {
            $callback($this->advertService());
        } finally {
            ob_end_clean();
        }
    }
}
