<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogOwnerIdQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogOwnerIdQueryTest extends IntegrationTestCase
{
    private ?GetCatalogOwnerIdQuery $getCatalogOwnerIdQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->getCatalogOwnerIdQuery = self::getContainer()->get(GetCatalogOwnerIdQuery::class);
    }

    public function testItReturnsCatalogOwnerId(): void
    {
        $user = $this->createUser('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');

        $catalogOwnerId = $this->getCatalogOwnerIdQuery->execute($catalogId);

        $this->assertEquals($user->getId(), $catalogOwnerId);
    }
}
