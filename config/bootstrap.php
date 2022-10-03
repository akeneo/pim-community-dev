<?php

use Akeneo\Platform\Component\Tenant\FirestoreContextFetcher;
use Akeneo\Platform\Component\Tenant\TenantContextDecoder;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
$dotenv->bootEnv(dirname(__DIR__) . '/.env');

if (isset($_ENV['APP_TENANT_ID']) && '' !== $_ENV['APP_TENANT_ID']) {
    $stream = match($_ENV['APP_ENV'] ?? '') {
        'prod' => 'php://stderr',
        default => __DIR__ . '/../var/logs/bootstrap.log',
    };
    $handler = new StreamHandler($stream, $_ENV['LOGGING_LEVEL'] ?? Logger::DEBUG);
    $jsonFormatter = new JsonFormatter();
    $jsonFormatter->includeStacktraces();
    $handler->setFormatter($jsonFormatter);

    $contextFetcher = new FirestoreContextFetcher(
        logger: new Logger('bootstrap', [$handler]),
        tenantContextDecoder: new TenantContextDecoder(),
        googleProjectId: $_ENV['FIRESTORE_PROJECT_ID']
    );
    $dotenv->populate(
        values: $contextFetcher->getTenantContext($_ENV['APP_TENANT_ID']),
        overrideExistingVars: true
    );
}

$_SERVER += $_ENV;
