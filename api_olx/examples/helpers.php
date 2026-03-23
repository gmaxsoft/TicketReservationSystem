<?php

use ServiceAdvert\Model\Advert;
use ServiceAdvert\Repository\Exception\FileReadException;
use ServiceAdvert\Service\AdvertService;

/**
 * @throws Exception
 */
function createValidAdvertLocal(AdvertService $service): Advert
{
    return $service->addLocal(getPayload());
}

/**
 * @throws Exception
 */
function createInvalidUrnAdvertLocal(AdvertService $service): Advert
{
    $advert = createValidAdvertLocal($service);
    $payload = $advert->getJsonPayload();
    $attributes = $payload['attributes'];
    unset($attributes[count($attributes) - 1]);
    $payload['attributes'] = $attributes;
    $advert->setJsonPayload($payload);
    $service->updateLocal($advert);

    return $advert;
}

/**
 * @throws Exception
 */
function createInvalidIdAdvertLocal(AdvertService $service): Advert
{
    $advert = createValidAdvertLocal($service);
    $payload = $advert->getJsonPayload();
    unset($payload['custom_fields']);
    $advert->setJsonPayload($payload);
    $service->updateLocal($advert);

    return $advert;
}

function setNetArea(Advert $advert, $value)
{
    $payload = $advert->getJsonPayload();

    $urn = [
        'urn' => 'urn:concept:net-area-m2',
        'value' => $value,
    ];

    $payload['attributes'][5] = $urn;

    $advert->setJsonPayload($payload);
}

function removeNetArea(Advert $advert)
{
    $payload = $advert->getJsonPayload();
    unset($payload['attributes'][5]);
    $advert->setJsonPayload($payload);
}

/**
 * @throws Exception
 */
function getPayload(): array
{
    $file = file_get_contents('payload.json');
    if (! $file) {
        throw new FileReadException('payload.json');
    }

    return json_decode($file, true);
}
