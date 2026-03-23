<?php

namespace ServiceAdvert\Api;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use OAuth2\Configuration;
use OAuth2\TokenManager\TokenManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ServiceAdvert\Api\Exception\InvalidRequestException;
use ServiceAdvert\Api\Exception\RequestException;
use ServiceAdvert\Api\Exception\UnexpectedResponseException;

class AdvertAPI
{
    protected Client $httpClient;

    protected TokenManager $tokenManager;

    protected Configuration $configuration;

    const ADVERT_PATH = 'advert/v1/';

    public function __construct(TokenManager $tokenManager, Configuration $configuration)
    {
        $this->tokenManager = $tokenManager;
        $this->configuration = $configuration;

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->configuration->getUserAgent(),
            'x-api-key' => $this->configuration->getApiKey(),
        ];

        $stack = HandlerStack::create();
        $stack->push($this->addBearerToken());

        $this->httpClient = new Client(
            [
                'headers' => $headers,
                'base_uri' => $this->configuration->getBaseUrl(),
                'handler' => $stack,
            ]
        );
    }

    /**
     * @throws RequestException
     */
    public function getAllMetadata(): array
    {
        return $this->responseToJson($this->makeRequest('GET', self::ADVERT_PATH.'meta'));
    }

    /**
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function getMetadata(string $uuid): array
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('uuid cannot be empty');
        }

        return $this->responseToJson($this->makeRequest('GET', self::ADVERT_PATH.$uuid.'/meta'));
    }

    /**
     * @throws RequestException
     */
    public function publishAdvert(array $payload): array
    {
        return $this->responseToJson($this->makeRequest('POST', self::ADVERT_PATH, [
            'json' => $payload,
        ]));
    }

    /**
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function updateAdvert(string $uuid, array $payload): array
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('uuid cannot be empty');
        }

        return $this->responseToJson($this->makeRequest('PUT', self::ADVERT_PATH.$uuid, [
            'json' => $payload,
        ]));
    }

    /**
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function deleteAdvert(string $uuid): array
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('uuid cannot be empty');
        }

        return $this->responseToJson($this->makeRequest('DELETE', self::ADVERT_PATH.$uuid));
    }

    /**
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function activateAdvert(string $uuid): array
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('uuid cannot be empty');
        }

        return $this->responseToJson($this->makeRequest('POST', self::ADVERT_PATH.$uuid.'/activate'));
    }

    /**
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function deactivateAdvert(string $uuid): array
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('uuid cannot be empty');
        }

        return $this->responseToJson($this->makeRequest('POST', self::ADVERT_PATH.$uuid.'/deactivate'));
    }

    /**
     * @throws RequestException
     */
    private function makeRequest(string $method, string $path, array $options = []): ResponseInterface
    {
        try {
            $res = $this->httpClient->request($method, $path, $options);
        } catch (ClientException $e) {
            // 400 errors: invalid code, wrong credentials, etc
            throw new InvalidRequestException($e->getResponse()->getBody()->getContents(), $e->getCode(), $e);
        } catch (ServerException $e) {
            // 500 errors
            throw new UnexpectedResponseException($e->getResponse()->getBody()->getContents(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            // other network exceptions
            throw new RequestException('Unknown exception when making request', $e->getCode(), $e);
        }

        return $res;
    }

    private function responseToJson(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        $response->getBody()->close();

        return json_decode($body, true);
    }

    private function addBearerToken(): Closure
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $accessToken = $this->tokenManager->getToken()->getAccessToken();
                $request = $request->withHeader('Authorization', 'Bearer '.$accessToken);

                return $handler($request, $options);
            };
        };
    }
}
