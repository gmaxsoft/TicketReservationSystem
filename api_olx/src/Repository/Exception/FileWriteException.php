<?php

namespace ServiceAdvert\Repository\Exception;

use Throwable;

class FileWriteException extends RepositoryException
{
    private string $filename;

    public function __construct(string $filename, $code = 0, ?Throwable $previous = null)
    {
        $this->filename = $filename;
        parent::__construct('Writing to file "'.$filename.'" failed.', $code, $previous);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
