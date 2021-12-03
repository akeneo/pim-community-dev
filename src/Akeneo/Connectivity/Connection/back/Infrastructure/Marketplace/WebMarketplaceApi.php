<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebMarketplaceApi implements WebMarketplaceApiInterface
{
    private ClientInterface $client;
    private WebMarketplaceAliasesInterface $webMarketplaceAliases;
    private LoggerInterface $logger;
    private string $fixturePath;
    private FeatureFlag $fakeAppsFeatureFlag;
    private Connection $dbConnection;

    public function __construct(
        ClientInterface $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        LoggerInterface $logger,
        FeatureFlag $fakeAppsFeatureFlag,
        Connection $dbConnection
    ) {
        $this->client = $client;
        $this->webMarketplaceAliases = $webMarketplaceAliases;
        $this->logger = $logger;
        $this->fakeAppsFeatureFlag = $fakeAppsFeatureFlag;
        $this->dbConnection = $dbConnection;
    }

    public function getExtensions(int $offset = 0, int $limit = 10): array
    {
        $edition = $this->webMarketplaceAliases->getEdition();
        $version = $this->webMarketplaceAliases->getVersion();

        $response = $this->client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'extension_type' => 'connector',
                'edition' => $edition,
                'version' => $version,
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getApps(int $offset = 0, int $limit = 10): array
    {
        if ($this->fakeAppsFeatureFlag->isEnabled()) {
            $sql = <<<SQL
SELECT `values` FROM pim_configuration WHERE code = 'fake-web-marketplace-json' LIMIT 1;
SQL;

            $fakeWebMarketplaceJson = $this->dbConnection->executeQuery($sql)->fetchOne();
            $fakeWebMarketplaceJson = false === $fakeWebMarketplaceJson
                ? '{"total": 0, "offset": 0, "limit": 120, "items": []}'
                : $fakeWebMarketplaceJson
            ;

            return json_decode($fakeWebMarketplaceJson, true);
        }

        $edition = $this->webMarketplaceAliases->getEdition();
        $version = $this->webMarketplaceAliases->getVersion();

        $response = $this->client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'extension_type' => 'app',
                'edition' => $edition,
                'version' => $version,
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getApp(string $id): ?array
    {
        $offset = 0;

        do {
            $result = $this->getApps($offset, 100);
            $offset += $result['limit'];

            foreach ($result['items'] as $item) {
                if ($id === $item['id']) {
                    return $item;
                }
            }
        } while (count($result['items']) > 0);

        return null;
    }

    public function validateCodeChallenge(string $appId, string $codeIdentifier, string $codeChallenge): bool
    {
        try {
            $response = $this->client->request('POST', sprintf('/api/1.0/app/%s/challenge', $appId), [
                'query' => [
                    'code_identifier' => $codeIdentifier,
                    'code_challenge' => $codeChallenge,
                ],
            ]);

            $payload = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $this->logger->warning(
                    'Marketplace rejected a code challenge request.',
                    ['response' => $response->getBody()->getContents()],
                );

                return false;
            }

            if (false === isset($payload['valid'])) {
                $this->logger->warning(
                    'Marketplace responded to a code challenge with an invalid payload.',
                    ['response' => $response->getBody()->getContents()],
                );

                return false;
            }

            return (bool) $payload['valid'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Exception thrown when validating a code challenge: %s', $e->getMessage()),
                ['exception' => $e]
            );

            return false;
        }
    }

    public function setFixturePath(string $fixturePath)
    {
        $this->fixturePath = $fixturePath;
    }
}
