<?php

namespace ServiceAdvert\Repository;

use Exception;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Repository\Exception\AlreadyExistsException;
use ServiceAdvert\Repository\Exception\FileWriteException;
use ServiceAdvert\Repository\Exception\NotFoundException;
use ServiceAdvert\Repository\Exception\RepositoryException;

/*
 * Simple repository that persists data to a file
 */
class FileRepository implements RepositoryInterface
{
    const ADVERTS_FILE = 'data/adverts.json';

    private string $filename;

    private array $adverts = [];

    /**
     * @throws RepositoryException
     */
    public function __construct($filename = '')
    {
        $this->filename = $filename ?: self::ADVERTS_FILE;
        $this->readFromFile();
    }

    /**
     * @throws AlreadyExistsException
     * @throws FileWriteException
     */
    public function create(Advert $advert)
    {
        if (! empty($this->adverts[$advert->getExternalId()])) {
            throw new AlreadyExistsException($advert->getExternalId());
        }
        $this->adverts[$advert->getExternalId()] = $advert;
        $this->writeToFile();
    }

    /**
     * @throws NotFoundException
     */
    public function read(string $externalId): Advert
    {
        if (empty($this->adverts[$externalId])) {
            throw new NotFoundException($externalId);
        }

        return $this->adverts[$externalId];
    }

    /**
     * @throws NotFoundException
     * @throws FileWriteException
     */
    public function update(Advert $advert)
    {
        if (empty($this->adverts[$advert->getExternalId()])) {
            throw new NotFoundException($advert->getExternalId());
        }
        $this->adverts[$advert->getExternalId()] = $advert;
        $this->writeToFile();
    }

    /**
     * @throws NotFoundException
     * @throws FileWriteException
     */
    public function delete(string $externalId)
    {
        if (empty($this->adverts[$externalId])) {
            throw new NotFoundException($externalId);
        }
        unset($this->adverts[$externalId]);
        $this->writeToFile();
    }

    /**
     * @throws RepositoryException
     */
    private function readFromFile()
    {
        $file = @file_get_contents($this->filename);
        if (! $file) {
            return;
        }
        $data = json_decode($file, true);

        $fileAdverts = [];
        foreach ($data as $advertData) {
            $advert = $this->createAdvert($advertData);
            $fileAdverts[$advert->getExternalId()] = $advert;
        }
        $this->adverts = $fileAdverts;
    }

    /**
     * @throws RepositoryException
     */
    private function createAdvert(array $data): Advert
    {
        try {
            return new Advert($data);
        } catch (Exception $e) {
            throw new RepositoryException($e);
        }
    }

    /**
     * @throws FileWriteException
     */
    private function writeToFile()
    {
        $json = json_encode($this->adverts);
        $length = file_put_contents($this->filename, $json);
        if (! $length) {
            throw new FileWriteException($this->filename);
        }
    }

    /**
     * Pretty Print all adverts in local storage
     */
    public function dump()
    {
        echo '<h2>Adverts List:<h2/>';
        foreach ($this->adverts as $advert) {
            echo $advert->prettyPrint();
            echo '<br/><br/>';
        }
    }
}
