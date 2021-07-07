<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebMarketplaceApi implements WebMarketplaceApiInterface
{
    private const EXTENSIONS_FILENAME = 'marketplace-data-extensions.json';
    private string $fixturePath;

    public function setFixturePath(string $fixturePath)
    {
        $this->fixturePath = $fixturePath;
    }

    public function getExtensions(string $edition, string $version, $offset = 0, $limit = 10): array
    {
        return json_decode(file_get_contents($this->fixturePath.self::EXTENSIONS_FILENAME), true);
    }
}
