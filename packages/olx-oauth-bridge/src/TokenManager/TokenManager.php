<?php

namespace OAuth2\TokenManager;

use OAuth2\Exception\StorageException;
use OAuth2\OAuth2;

class TokenManager
{
    public function __construct(
        private OAuth2 $oauth2,
        private FileTokenStorageService $storage,
        private ?string $authorizationCode = null,
    ) {
        if ($this->authorizationCode !== null) {
            $response = $this->oauth2->exchangeAuthorizationCode($this->authorizationCode);
            $token = Token::fromOAuthResponse($response);
            $this->storage->write($token->toArray());

            return;
        }

        $data = $this->storage->read();
        if ($data === null || ($data['access_token'] ?? '') === '') {
            throw new StorageException('No OAuth token in storage. Provide OLX authorization code once (config code) or copy a valid token file.');
        }
    }

    public function getToken(): Token
    {
        $data = $this->storage->read();
        if ($data === null) {
            throw new StorageException('OAuth token storage is empty.');
        }

        $token = new Token(
            (string) $data['access_token'],
            isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            (int) ($data['expires_at'] ?? 0),
        );

        if (! $token->isExpired()) {
            return $token;
        }

        $refresh = $token->getRefreshToken();
        if ($refresh === null || $refresh === '') {
            throw new StorageException('Access token expired and no refresh token is available.');
        }

        $response = $this->oauth2->refreshAccessToken($refresh);
        $newToken = Token::fromOAuthResponse($response, $token->getRefreshToken());
        $this->storage->write($newToken->toArray());

        return $newToken;
    }
}
