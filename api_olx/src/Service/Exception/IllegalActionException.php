<?php

namespace ServiceAdvert\Service\Exception;

class IllegalActionException extends NotAllowedException
{
    public function __construct(string $action, string $status)
    {
        parent::__construct('Action is not allowed in current status: '.$status.' -> '.$action);
    }
}
