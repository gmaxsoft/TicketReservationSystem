<?php

namespace ServiceAdvert\Service;

use Exception;
use ServiceAdvert\Api\AdvertAPI;
use ServiceAdvert\Api\Exception\InvalidRequestException;
use ServiceAdvert\Api\Exception\RequestException;
use ServiceAdvert\Model\Advert;
use ServiceAdvert\Model\Exception\InvalidActionStatus;
use ServiceAdvert\Model\Status\ActionStatus;
use ServiceAdvert\Model\Status\Code;
use ServiceAdvert\Model\Status\Status;
use ServiceAdvert\Repository\Exception\AlreadyExistsException;
use ServiceAdvert\Repository\Exception\NotFoundException;
use ServiceAdvert\Repository\RepositoryInterface;
use ServiceAdvert\Service\Exception\IllegalActionException;
use ServiceAdvert\Service\Exception\TransientStateException;

class AdvertService
{
    private const POST = 'POST';

    private const PUT = 'PUT';

    private const DELETE = 'DELETE';

    private const ACTIVATE = 'ACTIVATE';

    private const DEACTIVATE = 'DEACTIVATE';

    private RepositoryInterface $repository;

    private AdvertAPI $api;

    public function __construct(RepositoryInterface $repository, AdvertAPI $api)
    {
        $this->repository = $repository;
        $this->api = $api;
    }

    /**
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     */
    public function mustAllow(string $action, Advert $advert)
    {
        $actionStatus = $advert->getLastActionStatus();
        // Last action status reported by the Advert API cannot be in a transient state
        if (! empty($actionStatus) && ActionStatus::isTransient($actionStatus)) {
            throw new TransientStateException($action, $actionStatus);
        }

        $code = $advert->getStatus()->getCode();
        $allowedActions = $this->getAllowedActions($code, $actionStatus);
        if (! in_array($action, $allowedActions)) {
            throw new IllegalActionException($action, $code);
        }
    }

    public function getAllowedActions(string $statusCode, string $actionStatus): array
    {
        $allowedActions = [];
        switch ($statusCode) {
            case Code::ACTIVE:
                $allowedActions = [self::PUT, self::DELETE, self::DEACTIVATE];
                break;
            case Code::NOT_INIT: // NOT_INIT is a special local status for ads that have not yet been sent to the Advert API
            case Code::UNPAID:
            case Code::EMPTY_CODE:
            case Code::REMOVED_BY_PARENT_AD:
            case '':
                $allowedActions = [self::POST];
                break;
            case Code::OUTDATED_BY_PACKAGE:
                $allowedActions = [self::PUT, self::DELETE];
                break;
            case Code::REMOVED_BY_USER:
                // In the particular case the Advert Status Code is REMOVED_BY_USER, the next action depends on the previous action
                if ($actionStatus == ActionStatus::DEACTIVATED) {
                    $allowedActions = [self::ACTIVATE];
                } elseif ($actionStatus == ActionStatus::DELETED) {
                    $allowedActions = [self::POST];
                }
                break;
            case Code::MODERATED:
            case Code::OUTDATED:
            case Code::REMOVED_BY_MODERATOR:
            default:
        }

        return $allowedActions;
    }

    /**
     * @throws RequestException
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     */
    public function publish(Advert $advert)
    {
        $this->makeRequest(AdvertService::POST, $advert);
    }

    /**
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     * @throws RequestException
     */
    public function update(Advert $advert)
    {
        $this->makeRequest(AdvertService::PUT, $advert);
    }

    /**
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     * @throws RequestException
     */
    public function delete(Advert $advert)
    {
        $this->makeRequest(AdvertService::DELETE, $advert);
    }

    /**
     * @throws NotFoundException
     */
    public function deleteLocal(Advert $advert)
    {
        $this->repository->delete($advert->getExternalId());
    }

    /**
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     * @throws RequestException
     */
    public function activate(Advert $advert)
    {
        $this->makeRequest(AdvertService::ACTIVATE, $advert);
    }

    /**
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     * @throws RequestException
     */
    public function deactivate(Advert $advert)
    {
        $this->makeRequest(AdvertService::DEACTIVATE, $advert);
    }

    /**
     * @throws RequestException
     * @throws NotFoundException
     * @throws InvalidActionStatus
     * @throws TransientStateException
     * @throws IllegalActionException
     */
    private function makeRequest(string $action, Advert $advert)
    {
        $this->mustAllow($action, $advert);
        try {
            $body = $this->execute($action, $advert);

            $data = $body['data'];
            echo json_encode($body).'<br/>';

            $uuid = $data[Advert::UUID_FIELD_NAME];
            $advert->setUuid($uuid);

            $lastActionStatus = $data[Advert::LAST_ACTION_STATUS_FIELD_NAME];
            $lastActionAt = $data[Advert::LAST_ACTION_AT_FIELD_NAME];
            $advert->setLastAction($lastActionStatus, $lastActionAt);

            $advert->setLastError([]);
            $advert->setLastOperationError([]);

            $this->repository->update($advert);

            echo '<h4>Successful '.$action.' advert request</h4>';

        } catch (InvalidRequestException $e) {
            $bodyString = $e->getBody();
            echo $bodyString.'<br/>';

            $statusCode = $e->getCode();

            if ($statusCode == 400 || $statusCode == 404 || $statusCode == 409) {
                $body = json_decode($bodyString, true);

                $title = $body['message'];
                $errors = $body['errors'] ?? [];

                $data = [
                    'title' => $title,
                    'validation' => $errors,
                ];

                $advert->setLastOperationError($data);
                $this->repository->update($advert);
            }

            echo '<h4>Unsuccessful '.$action.' advert request</h4>';
            throw $e;
        }
    }

    /**
     * @throws RequestException
     * @throws Exception
     */
    public function execute(string $action, Advert $advert): array
    {
        switch ($action) {
            case AdvertService::POST:
                $data = $this->api->publishAdvert($advert->getJsonPayload());
                break;
            case AdvertService::PUT:
                $data = $this->api->updateAdvert($advert->getUuid(), $advert->getJsonPayload());
                break;
            case AdvertService::DELETE:
                $data = $this->api->deleteAdvert($advert->getUuid());
                break;
            case AdvertService::ACTIVATE:
                $data = $this->api->activateAdvert($advert->getUuid());
                break;
            case AdvertService::DEACTIVATE:
                $data = $this->api->deactivateAdvert($advert->getUuid());
                break;
            default:
                throw new Exception('Action not defined');
        }

        return $data;
    }

    /**
     * @throws NotFoundException
     * @throws RequestException
     * @throws Exception
     */
    public function syncMetadata(Advert $advert)
    {
        $uuid = $advert->getUuid();
        if (empty($uuid)) {
            echo '<h4>Unsuccessful sync metadata request</h4>';
            throw new Exception('uuid not defined');
        }

        $body = $this->api->getMetadata($advert->getUuid());
        $data = $body['data'];
        echo json_encode($body).'<br/>';

        $lastActionStatus = $data[Advert::LAST_ACTION_STATUS_FIELD_NAME];
        $lastActionAt = $data[Advert::LAST_ACTION_AT_FIELD_NAME];
        if ($lastActionStatus && $lastActionAt) {
            $advert->setLastAction($lastActionStatus, $lastActionAt);
        }

        $lastError = $data['last_error'] ?? [];
        $advert->setLastError($lastError);

        $statusData = $data['state'];
        if ($statusData) {
            $status = new Status($statusData['code'], $statusData);
            $advert->setStatus($status);
        }

        $this->repository->update($advert);
        echo '<h4>Successful sync metadata request</h4>';
    }

    /**
     * @throws NotFoundException
     */
    public function readLocal(string $externalId): Advert
    {
        return $this->repository->read($externalId);
    }

    public function addLocal(array $jsonPayload): Advert
    {
        // retry creating advert if an exception occurs
        while (true) {
            try {
                $externalId = uniqid();

                $jsonPayload[Advert::CUSTOM_FIELDS_NAME][Advert::ID_FIELD_NAME] = $externalId;

                $statusData = [
                    Status::CODE_FIELD_NAME => Code::NOT_INIT,
                ];

                $data = [
                    Advert::EXTERNAL_ID_FIELD_NAME => $externalId,
                    Advert::JSON_PAYLOAD_FIELD_NAME => $jsonPayload,
                    Advert::STATUS_FIELD_NAME => $statusData,
                ];

                $advert = new Advert($data);
                $this->repository->create($advert);

                return $advert;

            } catch (AlreadyExistsException $e) {
                // should never happen, but in any case
            }
        }
    }

    /**
     * @throws NotFoundException
     */
    public function updateLocal(Advert $advert)
    {
        $this->repository->update($advert);
    }

    public function dumpRepo(string $message)
    {
        echo '<h3>'.$message.':</h3>';
        $this->repository->dump();
    }
}
