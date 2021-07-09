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
    private WebMarketplaceAliasesInterface $webMarketplaceAliases;

    public function __construct(
        ClientInterface $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases
    ) {
        $this->client = $client;
        $this->webMarketplaceAliases = $webMarketplaceAliases;
    }

    public function getExtensions(int $offset = 0, int $limit = 10): array
    {
        $edition = $this->webMarketplaceAliases->getEdition();
        $version = $this->webMarketplaceAliases->getVersion();

        $response = $this->client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'edition' => $edition,
                'version' => $version,
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
