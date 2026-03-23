<?php

namespace OAuth2\Exception;

class MandatoryFieldsException extends \InvalidArgumentException
{
    /**
     * @param  array<int, string>  $fields
     */
    public function __construct(
        private array $fields,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message !== '' ? $message : 'Missing configuration fields: '.implode(', ', $fields), $code, $previous);
    }

    /**
     * @return array<int, string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
