<?php

namespace ServiceAdvert\Repository\Exception;

use Throwable;

class FileReadException extends RepositoryException
{
    private string $filename;

    public function __construct(string $filename, $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;
        parent::__construct($filename.' file not found or empty', $code, $previous);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
