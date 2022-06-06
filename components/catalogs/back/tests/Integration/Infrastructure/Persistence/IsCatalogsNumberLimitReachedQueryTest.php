<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\IsCatalogsNumberLimitReachedQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCatalogsNumberLimitReachedQueryTest extends IntegrationTestCase
{
    private ?IsCatalogsNumberLimitReachedQuery $query;
    private ?UpsertCatalogQuery $upsertQuery;
    private int $catalogsNumberMaxLimit;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(IsCatalogsNumberLimitReachedQuery::class);
        $this->upsertQuery = self::getContainer()->get(UpsertCatalogQuery::class);
        $this->catalogsNumberMaxLimit = self::getContainer()->getParameter('akeneo_catalog.max_limit');
    }

    public function testItReturnsTrueWhenTheCatalogsNumberLimitIsReached(): void
    {
        $ownerId = $this->createUser('owner')->getId();

        $this->assertFalse($this->query->execute($ownerId));

        for ($i = 0; $i < $this->catalogsNumberMaxLimit + 1; $i++) {
            $this->upsertQuery->execute(new Catalog(
                Uuid::uuid4()->toString(),
                'Store US',
                (int) $ownerId,
                false
            ));
        }

        $this->assertTrue($this->query->execute($ownerId));
    }
}
