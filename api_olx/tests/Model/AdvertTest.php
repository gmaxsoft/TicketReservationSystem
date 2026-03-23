<?php

namespace OAuth2\Test\Model;

use InvalidArgumentException;
use OAuth2\Exception\FileReadException;
use PHPUnit\Framework\TestCase;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Model\Status\Code;
use ServiceAdvert\Model\Status\Status;

class AdvertTest extends TestCase
{
    const VALID_FILE_NAME = 'tests/resources/payload.json';

    private static array $validJsonPayload;

    private static array $validJsonPayloadWithExternalId;

    private static array $localAdvertData;

    private static array $fullAdvertData;

    private static string $externalId = '1dm1do12oem';

    /**
     * @throws FileReadException
     */
    public static function setUpBeforeClass()
    {
        $file = @file_get_contents(self::VALID_FILE_NAME);
        if (! $file) {
            throw new FileReadException(self::VALID_FILE_NAME);
        }
        static::$validJsonPayload = json_decode($file, true);

        static::$validJsonPayloadWithExternalId = static::$validJsonPayload;
        static::$validJsonPayloadWithExternalId[Advert::CUSTOM_FIELDS_NAME][Advert::ID_FIELD_NAME] = static::$externalId;

        $statusData = [
            Status::CODE_FIELD_NAME => Code::NOT_INIT,
        ];

        static::$localAdvertData = [
            Advert::EXTERNAL_ID_FIELD_NAME => static::$externalId,
            Advert::STATUS_FIELD_NAME => $statusData,
            Advert::JSON_PAYLOAD_FIELD_NAME => static::$validJsonPayloadWithExternalId,
        ];

        $statusData = [
            Status::CODE_FIELD_NAME => Code::ACTIVE,
            Status::RAW_FIELD_NAME => ['other' => 'things'],
        ];

        static::$fullAdvertData = [
            Advert::EXTERNAL_ID_FIELD_NAME => static::$externalId,
            Advert::STATUS_FIELD_NAME => $statusData,
            Advert::JSON_PAYLOAD_FIELD_NAME => static::$validJsonPayloadWithExternalId,
            Advert::UUID_FIELD_NAME => '12312das',
            Advert::LAST_ACTION_STATUS_FIELD_NAME => 'POSTED',
            Advert::LAST_ACTION_AT_FIELD_NAME => 'DM1ODOM1',
            Advert::LAST_ERROR_FIELD_NAME => ['SOME' => 'DATA'],
            Advert::LAST_OP_ERROR_FIELD_NAME => ['OTHER' => 'DATA'],
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_i_should_create_local_advert_from_valid_data()
    {
        $advert = new Advert(static::$localAdvertData);

        $this->assertEquals(static::$externalId, $advert->getExternalId());
        $this->assertEquals(static::$validJsonPayloadWithExternalId, $advert->getJsonPayload());
        $this->assertEquals(Code::NOT_INIT, $advert->getStatus()->getCode());

        $this->assertEmpty($advert->getUuid());
        $this->assertEmpty($advert->getLastActionStatus());
        $this->assertEmpty($advert->getLastActionAt());
        $this->assertEmpty($advert->getLastError());
        $this->assertEmpty($advert->getLastOperationError());
    }

    public function test_i_should_raise_exception_when_creating_advert_without_external_id()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::EXTERNAL_ID_FIELD_NAME] = '';
        new Advert($data);

    }

    public function test_i_should_raise_exception_when_creating_advert_without_json_payload()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::JSON_PAYLOAD_FIELD_NAME] = '';
        new Advert($data);
    }

    public function test_i_should_raise_exception_when_creating_advert_without_status()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::STATUS_FIELD_NAME] = '';
        new Advert($data);
    }

    public function test_i_should_raise_exception_when_creating_advert_without_status_code()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::STATUS_FIELD_NAME][Status::CODE_FIELD_NAME] = '';
        new Advert($data);
    }

    public function test_i_should_raise_exception_when_creating_advert_without_custom_fields()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::JSON_PAYLOAD_FIELD_NAME][Advert::CUSTOM_FIELDS_NAME] = [];
        new Advert($data);
    }

    public function test_i_should_raise_exception_when_creating_advert_without_custom_fields_external_id()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = static::$localAdvertData;
        $data[Advert::JSON_PAYLOAD_FIELD_NAME][Advert::CUSTOM_FIELDS_NAME][Advert::ID_FIELD_NAME] = '';
        new Advert($data);
    }

    public function test_i_should_encode_and_decode()
    {
        $advert = new Advert(static::$fullAdvertData);

        $this->assertEquals(static::$fullAdvertData, json_decode(json_encode($advert), true));
    }
}
