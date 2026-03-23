<?php

namespace ServiceAdvert\Repository\Exception;

class AlreadyExistsException extends RepositoryException
{
    public function __construct(string $externalId)
    {
        parent::__construct('Advert with externalId "'.$externalId.'" already exists in repository');
    }
}
