<?php

use Akeneo\Platform\Component\Tenant\TenantContextLoader;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->usePutenv();
$dotenv->bootEnv(dirname(__DIR__) . '/.env');

function isUcsPlatform(): bool
{
    return (isset($_ENV['APP_TENANT_ID']) && '' !== $_ENV['APP_TENANT_ID'])
        && (isset($_ENV['FIRESTORE_PROJECT_ID']) && '' !== $_ENV['FIRESTORE_PROJECT_ID'])
        && (isset($_ENV['APP_TENANT_CONTEXT_COLLECTION_NAME']) && '' !== $_ENV['APP_TENANT_CONTEXT_COLLECTION_NAME'])
        && (isset($_ENV['APP_TENANT_CONTEXT_ENCRYPTION_KEY_PATH']) && '' !== $_ENV['APP_TENANT_CONTEXT_ENCRYPTION_KEY_PATH']);
}

if (isUcsPlatform()) {
    $loader = new TenantContextLoader();
    $loader->load($dotenv, __DIR__ . '/..');
}

$_SERVER += $_ENV;
