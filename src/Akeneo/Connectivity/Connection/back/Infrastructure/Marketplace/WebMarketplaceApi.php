<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use GuzzleHttp\ClientInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebMarketplaceApi implements WebMarketplaceApiInterface
{
    private ClientInterface $client;

    public function __construct(
        ClientInterface $client
    ) {
        $this->client = $client;
    }

    private function sanitizeEdition(string $edition): string
    {
        switch ($edition) {
            case 'GE':
                return 'growth-edition';
            case 'EE':
                return 'enterprise-edition';
            case 'Serenity':
                return 'serenity';
            case 'CE':
            default:
                return 'community-edition';
        }
    }

    private function sanitizeVersion(string $version): ?string
    {
        if (preg_match('|(\d.\d).\d|', $version, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getExtensions(string $edition, string $version, int $offset = 0, int $limit = 10): array
    {
        $response = $this->client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'edition' => $this->sanitizeEdition($edition),
                'version' => $this->sanitizeVersion($version),
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
