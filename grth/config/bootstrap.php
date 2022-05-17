<?php

use Akeneo\Platform\Component\Tenant\FirestoreContextFetcher;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
$dotenv->bootEnv(dirname(__DIR__) . '/.env');

if (isset($_ENV['APP_TENANT_ID']) && '' !== $_ENV['APP_TENANT_ID']) {
    $contextFetcher = new FirestoreContextFetcher(
        googleProjectId: $_ENV['GOOGLE_CLOUD_PROJECT'],
        keyFilePath: $_ENV['SRNT_GOOGLE_APPLICATION_CREDENTIALS']
    );
    $dotenv->populate(
        values: $contextFetcher->getTenantContext($_ENV['APP_TENANT_ID']),
        overrideExistingVars: true
    );
}

$_SERVER += $_ENV;
