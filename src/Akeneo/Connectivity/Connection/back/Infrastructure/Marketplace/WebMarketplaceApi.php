<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use GuzzleHttp\ClientInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebMarketplaceApi
{
    private const EXTENSIONS_FILENAME = 'marketplace-data-extensions.json';
    private const GET_EXTENSIONS_PATH = '/extensions';
    private ClientInterface $client;
    private string $fixturePath;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function setFixturePath(string $fixturePath)
    {
        $this->fixturePath = $fixturePath;
    }

    public function getExtensions(string $edition, string $version, $offset = 0, $limit = 10)
    {
        $data = file_get_contents($this->fixturePath . self::EXTENSIONS_FILENAME);

        return json_decode($data, true);
        /*
        return $this->client->request(self::GET_EXTENSIONS_PATH, [
            'edition' => $edition,
            'version' => $version,
            'offset' => $offset,
            'limit' => $limit,
        ]);*/
    }
}
