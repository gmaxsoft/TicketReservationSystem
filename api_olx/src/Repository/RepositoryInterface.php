<?php

namespace ServiceAdvert\Repository;

use ServiceAdvert\Model\Advert;
use ServiceAdvert\Repository\Exception\AlreadyExistsException;
use ServiceAdvert\Repository\Exception\NotFoundException;

interface RepositoryInterface
{
    /**
     * @throws AlreadyExistsException
     */
    public function create(Advert $advert);

    /**
     * @throws NotFoundException
     */
    public function read(string $externalId): Advert;

    /**
     * @throws NotFoundException
     */
    public function update(Advert $advert);

    /**
     * @throws NotFoundException
     */
    public function delete(string $externalId);
}
