<?php

namespace ServiceAdvert\Model\Status;

class Action
{
    private string $actionStatus;

    private string $actionAt;

    public function __construct(string $actionStatus, string $actionAt)
    {
        $this->actionStatus = $actionStatus;
        $this->actionAt = $actionAt;
    }

    public function getActionStatus(): string
    {
        return $this->actionStatus;
    }

    public function getActionAt(): string
    {
        return $this->actionAt;
    }
}
