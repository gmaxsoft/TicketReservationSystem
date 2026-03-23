<?php

namespace ServiceAdvert\Model;

use InvalidArgumentException;
use ServiceAdvert\Model\Status\Action;
use ServiceAdvert\Model\Status\Status;

class Advert implements \JsonSerializable
{
    const EXTERNAL_ID_FIELD_NAME = 'external_id';

    const UUID_FIELD_NAME = 'uuid';

    const STATUS_FIELD_NAME = 'status';

    const LAST_ACTION_STATUS_FIELD_NAME = 'last_action_status';

    const LAST_ACTION_AT_FIELD_NAME = 'last_action_at';

    const LAST_ERROR_FIELD_NAME = 'last_error_status';

    const LAST_OP_ERROR_FIELD_NAME = 'last_op_error_status';

    const JSON_PAYLOAD_FIELD_NAME = 'json_payload';

    const CUSTOM_FIELDS_NAME = 'custom_fields';

    const ID_FIELD_NAME = 'id';

    /**
     * @var string represents the advert identifier in the CRM
     */
    private string $externalId;

    /**
     * @var string represents the advert identifier in the Advert API
     */
    private string $uuid;

    /**
     * @var Status represents the last known status of the advert in the Advert API
     */
    private Status $status;

    /**
     * @var Action represents the last known action performed on the advert in the Advert API
     */
    private Action $lastAction;

    /**
     * @var array represents the last asynchronous error response to an operation in the Advert API
     */
    private array $lastError;

    /**
     * @var array represents the last synchronous error response to an operation in the Advert API
     */
    private array $lastOperationError;

    /**
     * @var array represents the json payload of the advert in the Advert API
     */
    private array $jsonPayload;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $data)
    {
        $this->setFields($data);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setFields(array $data)
    {
        if (empty($data[self::EXTERNAL_ID_FIELD_NAME])) {
            throw new InvalidArgumentException('externalId must not be empty');
        }

        if (empty($data[self::JSON_PAYLOAD_FIELD_NAME])) {
            throw new InvalidArgumentException('jsonPayload must not be empty');
        }

        if (empty($data[self::STATUS_FIELD_NAME])) {
            throw new InvalidArgumentException('status must not be empty');
        }

        if (empty($data[self::STATUS_FIELD_NAME][Status::CODE_FIELD_NAME])) {
            throw new InvalidArgumentException('status code must not be empty');
        }

        if (empty($data[self::JSON_PAYLOAD_FIELD_NAME][self::CUSTOM_FIELDS_NAME])
            || $data[self::JSON_PAYLOAD_FIELD_NAME][self::CUSTOM_FIELDS_NAME][self::ID_FIELD_NAME] != $data[self::EXTERNAL_ID_FIELD_NAME]) {
            throw new InvalidArgumentException('custom field id must be equal to external id');
        }

        $statusData = $data[self::STATUS_FIELD_NAME];
        $code = $statusData[Status::CODE_FIELD_NAME];
        $raw = $statusData[Status::RAW_FIELD_NAME] ?? [];

        // Mandatory
        $this->externalId = $data[self::EXTERNAL_ID_FIELD_NAME];
        $this->jsonPayload = $data[self::JSON_PAYLOAD_FIELD_NAME];
        $this->status = new Status($code, $raw);

        // Optional
        $this->uuid = $data[self::UUID_FIELD_NAME] ?? '';
        $this->lastAction = new Action($data[self::LAST_ACTION_STATUS_FIELD_NAME] ?? '', $data[self::LAST_ACTION_AT_FIELD_NAME] ?? '');
        $this->lastError = $data[self::LAST_ERROR_FIELD_NAME] ?? [];
        $this->lastOperationError = $data[self::LAST_OP_ERROR_FIELD_NAME] ?? [];
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getLastActionStatus(): string
    {
        return $this->lastAction->getActionStatus();
    }

    public function getLastActionAt(): string
    {
        return $this->lastAction->getActionAt();
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getLastError(): array
    {
        return $this->lastError;
    }

    public function getLastOperationError(): array
    {
        return $this->lastOperationError;
    }

    public function getJsonPayload(): array
    {
        return $this->jsonPayload;
    }

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function setLastAction(string $lastActionStatus, string $lastActionAt)
    {
        $this->lastAction = new Action($lastActionStatus, $lastActionAt);
    }

    public function setStatus(Status $status)
    {
        $this->status = $status;
    }

    public function setLastError(array $lastError)
    {
        $this->lastError = $lastError;
    }

    public function setLastOperationError(array $lastOperationError)
    {
        $this->lastOperationError = $lastOperationError;
    }

    public function setJsonPayload(array $jsonPayload)
    {
        $this->jsonPayload = $jsonPayload;
    }

    public function jsonSerialize(): array
    {
        return [
            self::EXTERNAL_ID_FIELD_NAME => $this->externalId,
            self::UUID_FIELD_NAME => $this->uuid,
            self::STATUS_FIELD_NAME => $this->status,
            self::LAST_ACTION_STATUS_FIELD_NAME => $this->lastAction->getActionStatus(),
            self::LAST_ACTION_AT_FIELD_NAME => $this->lastAction->getActionAt(),
            self::LAST_ERROR_FIELD_NAME => $this->lastError,
            self::LAST_OP_ERROR_FIELD_NAME => $this->lastOperationError,
            self::JSON_PAYLOAD_FIELD_NAME => $this->jsonPayload,
        ];
    }

    public function prettyPrint()
    {
        echo "<h3>Advert - $this->externalId</h3>";
        $this->printLine('external_id', $this->externalId);
        $this->printLine('uuid', $this->uuid);
        $this->printLine('last_action_status', $this->lastAction->getActionStatus());
        $this->printLine('last_action_at', $this->lastAction->getActionAt());
        $this->printLine('status', json_encode($this->status));
        $this->printLine('last_error', json_encode($this->lastError));
        $this->printLine('last_op_error', json_encode($this->lastOperationError));
        $this->printLine('json_payload', json_encode($this->jsonPayload));
    }

    public function printLine(string $title, string $data)
    {
        echo "<strong>$title</strong>: $data<br/>";
    }
}
