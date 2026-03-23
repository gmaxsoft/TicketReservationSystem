<?php

require_once __DIR__.'/initialize.php';
require_once __DIR__.'/helpers.php';

const NEW_LINE = '<br/>';

function exampleSyncMetaData()
{
    try {
        $service = initService();

        $service->dumpRepo('START --- From file');
        echo '<h1>--- Debug start ---</h1>';

        $advert = $service->readLocal('617bfeed71be9');
        $service->syncMetadata($advert);

        echo '<h1>--- Debug end ---</h1>';
        $service->dumpRepo('END --- To file');
    } catch (Exception $e) {
        echo $e->getMessage().NEW_LINE;
    }
}
