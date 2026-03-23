<?php

namespace ServiceAdvert\Model\Exception;

use Exception;

class InvalidActionStatus extends Exception
{
    public function __construct(string $actionStatus)
    {
        parent::__construct($actionStatus.' is not a valid action status');
    }
}
