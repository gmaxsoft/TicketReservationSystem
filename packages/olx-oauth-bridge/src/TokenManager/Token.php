<?php

namespace OAuth2\TokenManager;

class Token
{
    public function __construct(
        private string $accessToken,
        private ?string $refreshToken,
        private int $expiresAt,
    ) {}

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function isExpired(int $leewaySeconds = 60): bool
    {
        return time() >= ($this->expiresAt - $leewaySeconds);
    }

    /**
     * @return array{access_token: string, refresh_token: ?string, expires_at: int}
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromOAuthResponse(array $data, ?string $previousRefreshToken = null): self
    {
        $access = (string) ($data['access_token'] ?? '');
        $refresh = isset($data['refresh_token']) ? (string) $data['refresh_token'] : $previousRefreshToken;
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        return new self(
            $access,
            $refresh,
            time() + max(1, $expiresIn),
        );
    }
}
