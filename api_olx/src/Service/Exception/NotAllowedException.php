<?php

namespace ServiceAdvert\Service\Exception;

use Exception;

class NotAllowedException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct('Not allowed: '.$message);
    }
}
