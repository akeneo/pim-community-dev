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

namespace Akeneo\Pim\Enrichment\Product\Bundle\Migration;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrationAuthorization;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebClientMigrationAuthorization implements MigrationAuthorization
{
    private const WEB_URL = 'https://storage.googleapis.com/sku-to-uuid-instances-list/list.json';

    public function __construct(private string $env, private LoggerInterface $logger)
    {
    }

    public function isGranted(): bool
    {
        if (\in_array($this->env, ['test', 'dev'])) {
            $this->logger->notice('Client is authorized in the current env');

            return true;
        }

        $client = HttpClient::create();

        // Unique URL prevents cache
        $uniqueUrl = \sprintf('%s?%s', self::WEB_URL, (Uuid::uuid4())->toString());

        $response = $client->request(Request::METHOD_GET, $uniqueUrl);
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->logger->notice('Error while requesting web url', [
                'message' => $response->getContent(),
            ]);

            return false;
        }

        try {
            $content = $response->getContent();
            $decodedResponse = \json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $this->logger->notice('Error while decoding web url response', [
                'message' => $response->getContent(),
                'exception' => $e,
            ]);

            return false;
        }

        $client = $_ENV['GOOGLE_NAMESPACE'];
        $authorizedClients = \array_column($decodedResponse, 'name');
        if (!\in_array($client, $authorizedClients)) {
            $this->logger->notice('The client is not authorized to run the migration', [
                'client' => $client,
                'authorized_clients' => $authorizedClients,
                'http_response' => $decodedResponse,
            ]);

            return false;
        }

        return true;
    }
}
