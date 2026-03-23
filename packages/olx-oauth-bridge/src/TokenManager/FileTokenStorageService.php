<?php

namespace OAuth2\TokenManager;

class FileTokenStorageService
{
    public function __construct(
        private string $tokenFilePath,
    ) {}

    public function getPath(): string
    {
        return $this->tokenFilePath;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(): ?array
    {
        if (! is_readable($this->tokenFilePath)) {
            return null;
        }
        $raw = file_get_contents($this->tokenFilePath);
        if ($raw === false || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function write(array $data): void
    {
        $dir = dirname($this->tokenFilePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->tokenFilePath, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
