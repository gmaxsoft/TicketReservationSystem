<?php

namespace ServiceAdvert\Api\Exception;

use Throwable;

class UnexpectedResponseException extends RequestException
{
    private string $body;

    public function __construct(string $body, $code = 0, ?Throwable $previous = null)
    {
        $this->body = $body;
        parent::__construct($body, $code, $previous);
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
