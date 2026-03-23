<?php

namespace ServiceAdvert\Model\Status;

class Status implements \JsonSerializable
{
    const CODE_FIELD_NAME = 'code';

    const RAW_FIELD_NAME = 'raw';

    private string $code;

    private array $raw;

    /**
     * @param  string  $code  represents the status code
     * @param  array  $raw  represents the raw data from the Advert API
     */
    public function __construct(string $code, array $raw)
    {
        $this->code = $code;
        $this->raw = $raw;
    }

    public function jsonSerialize(): array
    {
        return [
            self::CODE_FIELD_NAME => $this->code,
            self::RAW_FIELD_NAME => $this->raw,
        ];
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}
