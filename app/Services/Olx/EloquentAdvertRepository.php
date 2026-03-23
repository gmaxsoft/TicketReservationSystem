<?php

namespace App\Services\Olx;

use App\Models\OlxAd;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Repository\Exception\AlreadyExistsException;
use ServiceAdvert\Repository\Exception\NotFoundException;
use ServiceAdvert\Repository\RepositoryInterface;

class EloquentAdvertRepository implements RepositoryInterface
{
    public function create(Advert $advert): void
    {
        if (OlxAd::query()->where('olx_external_id', $advert->getExternalId())->exists()) {
            throw new AlreadyExistsException($advert->getExternalId());
        }

        OlxAd::query()->create([
            'event_id' => $this->parseEventId($advert),
            'olx_external_id' => $advert->getExternalId(),
            'advert_data' => $advert->jsonSerialize(),
            'status' => 'draft',
            'olx_ad_id' => $advert->getUuid() !== '' ? $advert->getUuid() : null,
        ]);
    }

    public function read(string $externalId): Advert
    {
        $row = OlxAd::query()->where('olx_external_id', $externalId)->first();
        if ($row === null || $row->advert_data === null) {
            throw new NotFoundException($externalId);
        }

        /** @var array<string, mixed> $data */
        $data = $row->advert_data;

        return new Advert($data);
    }

    public function update(Advert $advert): void
    {
        $row = OlxAd::query()->where('olx_external_id', $advert->getExternalId())->first();
        if ($row === null) {
            throw new NotFoundException($advert->getExternalId());
        }

        $row->advert_data = $advert->jsonSerialize();
        if ($advert->getUuid() !== '') {
            $row->olx_ad_id = $advert->getUuid();
            $row->status = 'published';
        }
        $row->save();
    }

    public function delete(string $externalId): void
    {
        $deleted = OlxAd::query()->where('olx_external_id', $externalId)->delete();
        if ($deleted === 0) {
            throw new NotFoundException($externalId);
        }
    }

    private function parseEventId(Advert $advert): int
    {
        if (preg_match('/^evt-(\d+)-/', $advert->getExternalId(), $m)) {
            return (int) $m[1];
        }

        throw new \InvalidArgumentException('external_id musi mieć format evt-{id_wydarzenia}-...');
    }
}
