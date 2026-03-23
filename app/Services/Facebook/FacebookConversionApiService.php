<?php

namespace App\Services\Facebook;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookConversionApiService
{
    public function isConfigured(): bool
    {
        return config('facebook.pixel_id') !== ''
            && config('facebook.access_token') !== '';
    }

    /**
     * @param  array<string, mixed>  $customData
     * @param  array<string, mixed>  $userData
     */
    public function sendEvent(
        string $eventName,
        array $customData = [],
        array $userData = [],
        ?string $eventSourceUrl = null,
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $pixelId = config('facebook.pixel_id');
        $token = config('facebook.access_token');

        $eventPayload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'action_source' => 'website',
            'custom_data' => array_filter($customData),
            'user_data' => array_filter($userData),
        ];

        $eventPayload['event_source_url'] = $eventSourceUrl
            ?? (app()->runningInConsole() ? config('app.url') : url()->current());

        $query = ['access_token' => $token];
        if ($test = config('facebook.test_event_code')) {
            $query['test_event_code'] = $test;
        }

        $url = "https://graph.facebook.com/v21.0/{$pixelId}/events";

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->withQueryParameters($query)
                ->post($url, [
                    'data' => json_encode([$eventPayload]),
                ]);
            if (! $response->successful()) {
                Log::warning('Facebook CAPI: żądanie nieudane', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Facebook CAPI: wyjątek', ['message' => $e->getMessage()]);
        }
    }

    public function hashedEmail(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * @param  array<string, mixed>  $extraUserData
     */
    public function viewContent(int $eventId, string $eventName, string $eventSourceUrl, array $extraUserData = []): void
    {
        $this->sendEvent('ViewContent', [
            'content_ids' => [(string) $eventId],
            'content_name' => $eventName,
            'content_type' => 'product',
        ], $extraUserData, $eventSourceUrl);
    }

    /**
     * @param  array<string, mixed>  $extraUserData
     */
    public function initiateCheckout(float $value, string $currency, string $eventSourceUrl, array $extraUserData = []): void
    {
        $this->sendEvent('InitiateCheckout', [
            'currency' => $currency,
            'value' => $value,
        ], $extraUserData, $eventSourceUrl);
    }

    /**
     * @param  array<string, mixed>  $extraUserData
     */
    public function purchase(float $value, string $currency, string $email, ?string $eventSourceUrl = null, array $extraUserData = []): void
    {
        $userData = array_merge([
            'em' => [$this->hashedEmail($email)],
        ], $extraUserData);

        $this->sendEvent('Purchase', [
            'currency' => $currency,
            'value' => $value,
        ], $userData, $eventSourceUrl ?? config('app.url'));
    }
}
