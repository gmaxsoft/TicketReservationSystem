<?php

namespace App\Services\Olx;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use OAuth2\Configuration;
use OAuth2\TokenManager\TokenManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ServiceAdvert\Api\Exception\InvalidRequestException;
use ServiceAdvert\Api\Exception\RequestException;
use ServiceAdvert\Api\Exception\UnexpectedResponseException;

final class OlxPartnerHttpClient
{
    public function __construct(
        private TokenManager $tokenManager,
        private Configuration $configuration,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(string $path): array
    {
        $response = $this->makeRequest('GET', ltrim($path, '/'));

        return $this->responseToJson($response);
    }

    private function responseToJson(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        $response->getBody()->close();

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws RequestException
     * @throws InvalidRequestException
     * @throws UnexpectedResponseException
     */
    private function makeRequest(string $method, string $path, array $options = []): ResponseInterface
    {
        $client = $this->buildClient();
        try {
            return $client->request($method, $path, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $contents = $response !== null ? $response->getBody()->getContents() : '';

            throw new InvalidRequestException($contents, (int) $e->getCode(), $e);
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $contents = $response !== null ? $response->getBody()->getContents() : '';

            throw new UnexpectedResponseException($contents, (int) $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new RequestException('Unknown exception when making request', (int) $e->getCode(), $e);
        }
    }

    private function buildClient(): Client
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->configuration->getUserAgent(),
            'x-api-key' => $this->configuration->getApiKey(),
        ];

        $stack = HandlerStack::create();
        $stack->push($this->addBearerToken());

        return new Client([
            'headers' => $headers,
            'base_uri' => $this->configuration->getBaseUrl(),
            'handler' => $stack,
        ]);
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
