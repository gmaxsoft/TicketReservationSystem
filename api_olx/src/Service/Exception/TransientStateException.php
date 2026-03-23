<?php

namespace ServiceAdvert\Service\Exception;

class TransientStateException extends NotAllowedException
{
    public function __construct(string $action, string $action_status)
    {
        parent::__construct('No action is allowed on any transient state: '.$action_status.' -> '.$action);
    }
}
