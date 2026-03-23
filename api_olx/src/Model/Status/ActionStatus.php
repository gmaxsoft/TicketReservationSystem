<?php

namespace ServiceAdvert\Model\Status;

use ServiceAdvert\Model\Exception\InvalidActionStatus;

class ActionStatus
{
    const POSTED = 'POSTED';

    const NOT_POSTED = 'NOT_POSTED';

    const PUT = 'PUT';

    const NOT_PUT = 'NOT_PUT';

    const DELETED = 'DELETED';

    const NOT_DELETED = 'NOT_DELETED';

    const DEACTIVATED = 'DEACTIVATED';

    const NOT_DEACTIVATED = 'NOT_DEACTIVATED';

    const ACTIVATED = 'ACTIVATED';

    const NOT_ACTIVATED = 'NOT_ACTIVATED';

    /*
     * Transient action status
     */
    const TO_POST = 'TO_POST';

    const TO_PUT = 'TO_PUT';

    const TO_ACTIVATE = 'TO_ACTIVATE';

    const TO_DEACTIVATE = 'TO_DEACTIVATE';

    const TO_DELETE = 'TO_DELETE';

    private const TRANSITIVE_STATUS_LIST = [self::TO_POST, self::TO_PUT, self::TO_ACTIVATE, self::TO_DEACTIVATE, self::TO_DELETE];

    private const ACTION_STATUS_LIST = [
        self::POSTED,
        self::NOT_POSTED,

        self::PUT,
        self::NOT_PUT,

        self::ACTIVATED,
        self::NOT_ACTIVATED,

        self::DEACTIVATED,
        self::NOT_DEACTIVATED,

        self::DELETED,
        self::NOT_DELETED,

        self::TO_POST,
        self::TO_PUT,
        self::TO_ACTIVATE,
        self::TO_DEACTIVATE,
        self::TO_DELETE,
    ];

    /**
     * @throws InvalidActionStatus
     */
    public static function isTransient(string $actionStatus): bool
    {
        self::isValid($actionStatus);

        return in_array($actionStatus, self::TRANSITIVE_STATUS_LIST);
    }

    /**
     * @throws InvalidActionStatus
     */
    public static function isValid(string $actionStatus)
    {
        if (! in_array($actionStatus, self::ACTION_STATUS_LIST)) {
            throw new InvalidActionStatus($actionStatus);
        }
    }
}
