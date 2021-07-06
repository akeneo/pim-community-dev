<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Mock;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeWebMarketplaceApi implements WebMarketplaceApiInterface
{
    public function getExtensions(string $edition, string $version, $offset = 0, $limit = 10): array
    {
        throw new \LogicException('should not be called in an integration test');
    }
}
