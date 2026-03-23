<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Partner API JSON config (jak w api_olx/config.json.example)
    |--------------------------------------------------------------------------
    */
    'config_path' => env('OLX_CONFIG_PATH', storage_path('app/olx/config.json')),

    'token_path' => env('OLX_TOKEN_PATH', storage_path('app/olx/oauth_token.json')),

    /*
    |--------------------------------------------------------------------------
    | Szablon payloadu ogłoszenia (JSON Partner API)
    |--------------------------------------------------------------------------
    | Użyj placeholderów: {{title}}, {{description}}, {{price_value}}, {{available_seats}}, {{event_date}}
    */
    'payload_template_path' => env(
        'OLX_PAYLOAD_TEMPLATE_PATH',
        resource_path('olx/payload_template.json')
    ),

    'currency' => env('OLX_PRICE_CURRENCY', 'PLN'),

    'site_urn' => env('OLX_SITE_URN', 'urn:site:olxpl'),

    'category_urn' => env('OLX_CATEGORY_URN', 'urn:concept:CHANGE_ME_SET_CATEGORY'),

    /*
    |--------------------------------------------------------------------------
    | Wiadomości / czat (endpoint zależy od dokumentacji Partner API dla Twojej aplikacji)
    |--------------------------------------------------------------------------
    | Pusty = fetchMessages() zwraca pustą tablicę i loguje ostrzeżenie.
    | Możesz użyć %s jako placeholder UUID ogłoszenia (advert/v1).
    */
    'chat_messages_path' => env('OLX_CHAT_MESSAGES_PATH', ''),

];
