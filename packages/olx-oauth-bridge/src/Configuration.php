<?php

namespace OAuth2;

use OAuth2\Exception\FileReadException;
use OAuth2\Exception\MandatoryFieldsException;

class Configuration
{
    private const REQUIRED = ['client_id', 'client_secret', 'api_key'];

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data,
    ) {
        $missing = [];
        foreach (self::REQUIRED as $key) {
            if (empty($this->data[$key])) {
                $missing[] = $key;
            }
        }
        if ($missing !== []) {
            throw new MandatoryFieldsException($missing);
        }
    }

    /**
     * @throws FileReadException
     */
    public static function fromFile(string $path): self
    {
        if (! is_readable($path)) {
            throw new FileReadException('Cannot read OLX config file: '.$path);
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new FileReadException('Cannot read OLX config file: '.$path);
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new FileReadException('Invalid JSON in OLX config file: '.$path);
        }

        return new self($decoded);
    }

    public function getClientId(): string
    {
        return (string) $this->data['client_id'];
    }

    public function getClientSecret(): string
    {
        return (string) $this->data['client_secret'];
    }

    public function getApiKey(): string
    {
        return (string) $this->data['api_key'];
    }

    public function getBaseUrl(): string
    {
        return rtrim((string) ($this->data['base_url'] ?? 'https://api.olxgroup.com'), '/').'/';
    }

    public function getOauth2Path(): string
    {
        return ltrim((string) ($this->data['oauth2_path'] ?? 'oauth/v1/token'), '/');
    }

    public function getUserAgent(): string
    {
        return (string) ($this->data['user_agent'] ?? 'LaravelTicketReservation/1.0');
    }

    public function getCode(): ?string
    {
        $code = $this->data['code'] ?? null;

        return $code !== null && $code !== '' ? (string) $code : null;
    }
}
