<?php

namespace OAuth2\Test\Repository;

use PHPUnit\Framework\TestCase;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Model\Status\Code;
use ServiceAdvert\Model\Status\Status;
use ServiceAdvert\Repository\Exception\FileReadException;
use ServiceAdvert\Repository\Exception\FileWriteException;
use ServiceAdvert\Repository\Exception\NotFoundException;
use ServiceAdvert\Repository\Exception\RepositoryException;
use ServiceAdvert\Repository\FileRepository;

class FileRepositoryTest extends TestCase
{
    const FILE_PREFIX = 'tests/resources/generated/';

    const VALID_FILE_NAME = 'tests/resources/payload.json';

    const ADVERTS_REPO_FILE_NAME = 'tests/resources/adverts-repo.json';

    const EXTERNAL_ID = '1dm1do12oem';

    private static array $validJsonPayloadWithExternalId;

    private static Advert $localAdvert;

    /**
     * @throws FileReadException
     */
    public static function setUpBeforeClass()
    {
        $file = @file_get_contents(self::VALID_FILE_NAME);
        if (! $file) {
            throw new FileReadException(self::VALID_FILE_NAME);
        }
        $validJsonPayload = json_decode($file, true);

        static::$validJsonPayloadWithExternalId = $validJsonPayload;
        static::$validJsonPayloadWithExternalId[Advert::CUSTOM_FIELDS_NAME][Advert::ID_FIELD_NAME] = self::EXTERNAL_ID;

        $statusData = [
            Status::CODE_FIELD_NAME => Code::NOT_INIT,
        ];

        $localAdvertData = [
            Advert::EXTERNAL_ID_FIELD_NAME => self::EXTERNAL_ID,
            Advert::STATUS_FIELD_NAME => $statusData,
            Advert::JSON_PAYLOAD_FIELD_NAME => static::$validJsonPayloadWithExternalId,
        ];

        static::$localAdvert = new Advert($localAdvertData);
    }

    /**
     * @throws RepositoryException
     * @throws FileReadException
     */
    public function test_i_should_create_advert()
    {
        $filename = self::FILE_PREFIX.'adverts-'.time().'.json';
        $repo = new FileRepository($filename);
        $repo->create(static::$localAdvert);

        $file = @file_get_contents($filename);
        $this->assertNotFalse($file);

        $data = json_decode($file, true);
        $advertData = $data[self::EXTERNAL_ID];
        $this->assertNotEmpty($advertData);

        $advertFromFile = new Advert($advertData);
        $this->assertEquals(static::$localAdvert, $advertFromFile);

        @unlink($filename);
    }

    /**
     * @throws NotFoundException
     * @throws FileWriteException
     * @throws RepositoryException
     */
    public function test_i_should_update_advert()
    {
        $filename = static::FILE_PREFIX.'adverts-'.time().'.json';
        copy(static::ADVERTS_REPO_FILE_NAME, $filename);

        // Get Advert
        $repo = new FileRepository($filename);
        $advert = $repo->read(self::EXTERNAL_ID);

        // Update Advert
        $advert->setLastError(['some' => 'message']);
        $repo->update($advert);

        // Recreate repo with same file to ensure new content is read
        $repo = new FileRepository($filename);
        $advert = $repo->read(self::EXTERNAL_ID);

        $this->assertEquals(['some' => 'message'], $advert->getLastError());

        @unlink($filename);
    }

    /**
     * @throws NotFoundException
     */
    public function test_i_should_read_advert()
    {
        $repo = new FileRepository(static::ADVERTS_REPO_FILE_NAME);

        $advert = $repo->read(self::EXTERNAL_ID);

        $this->assertEquals(self::EXTERNAL_ID, $advert->getExternalId());
        $this->assertEquals(static::$validJsonPayloadWithExternalId, $advert->getJsonPayload());
        $this->assertEquals('12eh1don1od2no12dnd', $advert->getUuid());
    }

    /**
     * @throws RepositoryException
     * @throws FileWriteException
     */
    public function test_i_should_delete_advert()
    {
        $this->expectException(NotFoundException::class);

        $filename = static::FILE_PREFIX.'adverts-'.time().'.json';
        copy(static::ADVERTS_REPO_FILE_NAME, $filename);

        // Create repo
        $repo = new FileRepository($filename);

        // Delete Advert
        $repo->delete(self::EXTERNAL_ID);

        // Recreate repo with same file to ensure new content is read
        $repo = new FileRepository($filename);
        @unlink($filename);
        $repo->read(self::EXTERNAL_ID);
    }

    /**
     * @throws RepositoryException
     */
    public function test_i_should_raise_error_not_found()
    {
        $this->expectException(NotFoundException::class);
        $filename = static::FILE_PREFIX.'adverts-'.time().'.json';
        $repo = new FileRepository($filename);

        $repo->read('does_not_exist');
    }
}
