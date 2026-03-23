<?php

namespace ServiceAdvert\Repository\Exception;

class NotFoundException extends RepositoryException
{
    public function __construct(string $externalId)
    {
        parent::__construct('Advert with externalId "'.$externalId.'" not found in repository');
    }
}
