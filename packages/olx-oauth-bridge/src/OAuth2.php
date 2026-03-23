<?php

namespace OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OAuth2\Exception\RequestException;

class OAuth2
{
    public function __construct(
        private Configuration $configuration,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function requestToken(array $body): array
    {
        $url = $this->configuration->getBaseUrl().$this->configuration->getOauth2Path();
        $basic = base64_encode($this->configuration->getClientId().':'.$this->configuration->getClientSecret());

        $client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.$basic,
                'x-api-key' => $this->configuration->getApiKey(),
                'User-Agent' => $this->configuration->getUserAgent(),
            ],
        ]);

        try {
            $response = $client->post($url, ['json' => $body]);
        } catch (GuzzleException $e) {
            throw new RequestException('OAuth2 token request failed: '.$e->getMessage(), (int) $e->getCode(), $e);
        }

        $contents = $response->getBody()->getContents();
        $decoded = json_decode($contents, true);
        if (! is_array($decoded)) {
            throw new RequestException('OAuth2 token response is not JSON: '.$contents, $response->getStatusCode());
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        return $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }
}
