<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\IsCatalogsNumberLimitReachedQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCatalogsNumberLimitReachedQueryTest extends IntegrationTestCase
{
    private ?IsCatalogsNumberLimitReachedQuery $query;
    private ?int $catalogsNumberMaxLimit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(IsCatalogsNumberLimitReachedQuery::class);
        $this->catalogsNumberMaxLimit = self::getContainer()->getParameter('akeneo_catalog.max_number_of_catalogs_per_user');
    }

    public function testItReturnsTrueWhenTheCatalogsNumberLimitIsReached(): void
    {
        $this->createUser('shopifi');

        $this->assertFalse($this->query->execute('shopifi'));
        for ($i = 0; $i < $this->catalogsNumberMaxLimit; $i++) {
            $this->createCatalog(
                Uuid::uuid4()->toString(),
                "Store $i",
                'shopifi',
            );
        }

        $this->assertTrue($this->query->execute('shopifi'));
    }
}
