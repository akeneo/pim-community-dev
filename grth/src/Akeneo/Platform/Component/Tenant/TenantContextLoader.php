<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant;

use Akeneo\Platform\Component\Tenant\Exception\TenantContextInvalidFormatException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotFoundException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotReadyException;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class TenantContextLoader
{
    public function load(Dotenv $dotenv, string $appRootDir): void
    {
        $tenantId = $_ENV['APP_TENANT_ID'];
        $contextCollectionName = $_ENV['APP_TENANT_CONTEXT_COLLECTION_NAME'];
        $googleProjectId = $_ENV['FIRESTORE_PROJECT_ID'];
        $encryptionKeyPath = $_ENV['APP_TENANT_CONTEXT_ENCRYPTION_KEY_PATH'];

        $errorFileDirectory = sprintf('%s/config/errors', $appRootDir);
        $logger = $this->createLogger($appRootDir);

        if (!$tenantId || !$contextCollectionName || !$googleProjectId || !$encryptionKeyPath) {
            $logger->critical(
                'Error while initializing context: missing environment variable',
                [
                    'tenantId' => $tenantId,
                    'contextCollectionName' => $contextCollectionName,
                    'googleProjectId' => $googleProjectId,
                    'encryptionKeyPath' => $encryptionKeyPath,
                ]
            );
            $this->displayError(500, $errorFileDirectory);
        }

        try {
            $encryptionKey = trim(file_get_contents($encryptionKeyPath));

            $contextFetcher = new FirestoreContextFetcher(
                logger: $logger,
                tenantContextDecoder: new TenantContextDecoder($encryptionKey),
                googleProjectId: $googleProjectId,
                collection: $contextCollectionName,
            );
            $dotenv->populate(
                values: $contextFetcher->getTenantContext($tenantId),
                overrideExistingVars: true
            );
        } catch (TenantContextNotFoundException|TenantContextInvalidFormatException $e) {
            $logger->critical(sprintf('Not found or invalid context: %s', $e->getMessage()));
            $this->displayError(404, $errorFileDirectory);
        } catch (TenantContextNotReadyException $e) {
            $logger->critical(sprintf('Context status not ready: %s', $e->getMessage()));
            $this->displayError(500, $errorFileDirectory);
        } catch (Throwable $e) {
            $logger->critical(sprintf('Error while initializing context: %s', $e->getMessage()));
            $this->displayError(500, $errorFileDirectory);
        }
    }

    private function createLogger(string $appRootDir): Logger
    {
        $stream = match ($_ENV['APP_ENV'] ?? '') {
            'prod' => 'php://stderr',
            default => sprintf('%s/var/logs/bootstrap.log', $appRootDir),
        };
        $handler = new StreamHandler($stream, $_ENV['LOGGING_LEVEL'] ?? Logger::DEBUG);
        $jsonFormatter = new JsonFormatter();
        $jsonFormatter->includeStacktraces();
        $handler->setFormatter($jsonFormatter);

        return new Logger('bootstrap', [$handler]);
    }

    private function displayError(int $errorCode, string $errorFileDirectory): void
    {
        $errorFilePath = sprintf(__DIR__ . '/errors/error-%d.html', $errorCode);

        if (!file_exists($errorFilePath)) {
            $errorFilePath = $errorFileDirectory . '/error-500.html';
        }

        $response = new Response(file_get_contents($errorFilePath), $errorCode);
        $response->send();
        exit();
    }
}
