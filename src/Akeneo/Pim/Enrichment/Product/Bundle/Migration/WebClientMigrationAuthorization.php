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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class WebClientMigrationAuthorization implements MigrationAuthorization
{
    private const BLACKLIST_URL = 'https://storage.googleapis.com/sku-to-uuid-instances-list/list_2cdc822e-bc1f-48c5-940d-b95815abce6e.json';

    public function __construct(private string $env, private LoggerInterface $logger, private string $clientName)
    {
    }

    public function isGranted(): bool
    {
        if (\in_array($this->env, ['test', 'dev'])) {
            $this->logger->notice('Client is authorized in the current env');

            return true;
        }


        // Unique URL prevents cache
        $uniqueUrl = \sprintf('%s?%s', self::BLACKLIST_URL, (Uuid::uuid4())->toString());

        $client = new Client();
        try {
            $response = $client->request('GET', $uniqueUrl);
        } catch (GuzzleException $e) {
            $this->logger->error('Error while requesting web url', [
                'exception' => $e,
            ]);

            return false;
        }

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->logger->error('Error while requesting web url', [
                'message' => $response->getBody()->getContents(),
            ]);

            return false;
        }

        try {
            $content = $response->getBody()->getContents();
            $decodedResponse = \json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            $this->logger->error('Error while decoding web url response');

            return false;
        }

        $unauthorizedClients = \array_column($decodedResponse, 'name');
        if (\in_array($this->clientName, $unauthorizedClients)) {
            $this->logger->notice(
                \sprintf('The "%s" client is not authorized to run the migration', $this->clientName),
                ['clientName' => $this->clientName]
            );

            return false;
        }

        return true;
    }
}
