<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\GetAllExtensionsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsQueryIntegration extends TestCase
{
    private GetAllExtensionsQuery $getAllExtensionsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAllExtensionsQuery = self::$container->get(GetAllExtensionsQuery::class);
    }

    public function test_to_get_all_extensions()
    {
        $result = $this->getAllExtensionsQuery->execute();

        $this->assertEquals(120, $result->count());
        $this->assertEquals(120, count($result->extensions()));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
