<?php

use OAuth2\Configuration;
use OAuth2\Exception\FileReadException;
use OAuth2\Exception\MandatoryFieldsException;
use OAuth2\Exception\RequestException;
use OAuth2\Exception\StorageException;
use OAuth2\OAuth2;
use OAuth2\TokenManager\FileTokenStorageService;
use OAuth2\TokenManager\TokenManager;
use ServiceAdvert\Api\AdvertAPI;
use ServiceAdvert\Repository\FileRepository;
use ServiceAdvert\Service\AdvertService;

/**
 * @throws FileReadException
 * @throws MandatoryFieldsException
 * @throws MandatoryFieldsException
 * @throws RequestException
 * @throws StorageException
 */
function initService(): AdvertService
{
    $config = initConfig();
    $provider = initOAuth($config);
    $tokenManager = initTokenManager($config, $provider);

    $repository = new FileRepository;
    $api = new AdvertAPI($tokenManager, $config);

    return new AdvertService($repository, $api);
}

/**
 * @throws FileReadException
 * @throws MandatoryFieldsException
 */
function initConfig(): Configuration
{
    try {
        return new Configuration;
    } catch (FileReadException $e) {
        echo '<h3>Please provide a configuration file</h3>';
        echo 'Debug message: '.$e->getMessage().NEW_LINE;
        throw $e;
    } catch (MandatoryFieldsException $e) {
        echo '<h3>Please provide all the required fields</h3>';
        echo 'Missing fields: '.implode(', ', $e->getFields()).NEW_LINE;
        echo 'Debug message: '.$e->getMessage().NEW_LINE;
        throw $e;
    }
}

function initOAuth(Configuration $config): OAuth2
{
    return new OAuth2($config);
}

/**
 * @throws MandatoryFieldsException
 * @throws RequestException
 * @throws StorageException
 */
function initTokenManager(Configuration $config, OAuth2 $provider): TokenManager
{

    $code = $config->getCode();
    $tokenFile = FileTokenStorageService::TOKEN_FILE;
    $storageService = new FileTokenStorageService($tokenFile);

    try {
        echo '<p>Attempting to get token from file "'.$tokenFile.'"...</p>';
        $tokenManager = new TokenManager($provider, $storageService);
    } catch (StorageException $ex) {
        echo '<p>Invalid token file. Trying code...</p>';
        if (! empty($code)) {
            $tokenManager = new TokenManager($provider, $storageService, $code);
        } else {
            echo '<p>Please provide a code in the configuration file</p>';
            throw $ex;
        }
    }

    echo '<p><strong>Successfully initialized TokenManager</strong></p>';

    return $tokenManager;
}
